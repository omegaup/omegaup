#/usr/bin/php -d max_execution_time=7200
#!/usr/bin/python3

import argparse
import getpass
import json
import hashlib
import MySQLdb
import sys
import time

CONFIDENCE = 5
MAX_NUM_TOPICS = 5

def getGlobalDifficultyAndAverage(db):
    cur = db.cursor()
    cur.execute("""SELECT `QualityNominations`.`contents`
                   FROM `QualityNominations`
                   WHERE (`nomination` = 'suggestion');""")
    qualitySum = 0
    qualityN = 0
    difficultySum = 0
    difficultyN = 0

    for row in cur:
        contents = json.loads(row[0])
        if 'quality' in contents:
            qualitySum += contents['quality']
            qualityN += 1
        if 'difficulty' in contents:
            difficultySum += contents['difficulty']
            difficultyN += 1
    cur.close()

    return (qualitySum / float(qualityN), difficultySum / float(difficultyN))

def getProblemAggregates(db, problemId):
    cur = db.cursor()
    cur.execute("""SELECT `QualityNominations`.`contents`
                   FROM `QualityNominations` 
                   WHERE `nomination` = 'suggestion' 
                     AND `QualityNominations`.`problem_id` = %s;""" % problemId)
    qualitySum = 0
    qualityN = 0
    difficultySum = 0
    difficultyN = 0
    tagVotes = {}
    tagVotesN = 0
    for row in cur:
        contents = json.loads(row[0])
        if 'quality' in contents:
            qualitySum += contents['quality']
            qualityN += 1
        if 'difficulty' in contents:
            difficultySum += contents['difficulty']
            difficultyN += 1
        if 'tags' in contents:
            for tag in contents['tags']:
                if tag not in tagVotes:
                    tagVotes[tag] = 1
                else:
                    tagVotes[tag] += 1
                tagVotesN += 1
    cur.close()

    return (qualitySum, qualityN, difficultySum, difficultyN, tagVotes, tagVotesN)

def bayesianAverage(aprioriAverage, sum, n):
    if n < CONFIDENCE:
        return None
    return (CONFIDENCE * aprioriAverage + sum) / (CONFIDENCE + n)

def mostVotedTags(problemTagVotes, problemTagVotesN):
    if problemTagVotesN < 5:
        return None
    maximum = problemTagVotes[max(problemTagVotes, key=problemTagVotes.get)]
    finalTags = [tag for (tag, votes) in problemTagVotes.iteritems() if (votes >= 0.25 * maximum)]
    if len(finalTags) >= MAX_NUM_TOPICS:
        return None
    else:
        return finalTags

def replaceAutogeneratedTags(db, problemId, problemTags):
    db.query("DELETE FROM Problems_Tags WHERE problem_id = %s AND autogenerated = 1;" % problemId);
    db.commit();

    for tag in problemTags:
        cur = db.cursor()
        cur.execute("SELECT tag_id FROM Tags WHERE name = '%s';" % tag)
        tagId = cur.fetchone()[0]
        db.query("""INSERT INTO Problems_Tags (`problem_id`, `tag_id`, `public`, `autogenerated`) 
                    VALUES (%s, %s, %s, %s);""" % (problemId, tagId, 1, 1))
        db.commit()

def aggregateFeedback(db):
    globalQualityAverage, globalDifficultyAverage = getGlobalDifficultyAndAverage(db)

    cur = db.cursor()
    cur.execute("""SELECT DISTINCT `QualityNominations`.`problem_id`
                   FROM `QualityNominations`
                   WHERE nomination = 'suggestion';""")
    for row in cur:
        problemId = row[0]

        (problemQualitySum, problemQualityN,
        problemDifficultySum, problemDifficultyN,
        problemTagVotes, problemTagVotesN) = getProblemAggregates(db, problemId)

        problemQuality = bayesianAverage(globalQualityAverage, problemQualitySum, problemQualityN)
        problemDifficulty = \
                bayesianAverage(globalDifficultyAverage, problemDifficultySum, problemDifficultyN)
        if problemQuality != None or problemDifficulty != None:
            db.query("UPDATE `Problems` SET quality = %s, difficulty = %s WHERE problem_id = %s;"
                    % (problemQuality, problemDifficulty, problemId))
            db.commit();

        # TODO(heduenas): Get threshold parameter from DB for each problem independently.
        problemTags = mostVotedTags(problemTagVotes, problemTagVotesN)
        if len(problemTags):
            replaceAutogeneratedTags(db, problemId, problemTags) # two operations in one transaction https://stackoverflow.com/questions/12378227/mysqldb-with-multiple-transaction-per-connection
    cur.close()

def main():
    parser = argparse.ArgumentParser(description='Aggregate user feedback.')

    parser.add_argument('--user', type=str, help='MySQL username', required=True)
    parser.add_argument('--database', type=str, help='MySQL database', required=True)
    parser.add_argument('--password', type=str, help='MySQL password')

    args = parser.parse_args()
    password = args.password
    if not password: 
	    password = getpass.getpass()

    db = MySQLdb.connect(
	    host='localhost',
	    user=args.user,
	    passwd=password,
	    db=args.database
    )

    aggregateFeedback(db)
    db.close()

if __name__ == '__main__':
  main()
