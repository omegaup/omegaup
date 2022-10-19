# SQL Queries
GET_PLAGIARISM_TABLE = '''
                        '''
def test_check():
    self.assertTrue(True)

def main():
    parser = argparse.ArgumentParser(
        description='Tests the Plagiarism Detector')
    parser.add_argument('--local-downloader-dir')

    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    args = parser.parse_args()
    args.verbose = True
    lib.logs.init(parser.prog, args)

    logging.info('started')
    dbconn = lib.db.connect(lib.db.DatabaseConnectionArguments.from_args(args))
    
    # create a contest 
    # first run the plagiarism_detector.py
    # then check if there are correct amount of entries in the database for each contest
    # then check if there are all values correctly in the database.
    test_check()

if __name__ == '__main__':
    main()
