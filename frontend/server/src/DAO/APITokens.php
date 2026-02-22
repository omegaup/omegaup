<?php

namespace OmegaUp\DAO;

/**
 * APITokens Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\APITokens}.
 *
 * @psalm-type IdentityExt=array{classname: string, country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string}
 * @psalm-type AuthIdentityExt=array{currentIdentity: IdentityExt, loginIdentity: IdentityExt}
 *
 * @access  public
 * @package OmegaUp\DAO
 */
class APITokens extends \OmegaUp\DAO\Base\APITokens {
    /**
     * @return array{currentIdentity: IdentityExt, loginIdentity: IdentityExt, apiTokenId: int}|null
     */
    public static function getIdentityByToken(
        string $apiToken,
        ?string $username
    ) {
        $sql = "
            SELECT
                at.apitoken_id,
                1 as is_main_identity,
                i.*,
                IFNULL(ur.classname, 'user-rank-unranked') AS classname
            FROM
                `API_Tokens` at
            INNER JOIN
                `Users` u ON u.user_id = at.user_id
            INNER JOIN
                `Identities` i ON i.identity_id = u.main_identity_id
            LEFT JOIN
                User_Rank ur ON ur.user_id = i.user_id
            WHERE
                at.token = ?
        ";
        $params = [$apiToken];
        if ($username !== null) {
            $sql .= "
                UNION
                SELECT
                    at.apitoken_id,
                    0 as is_main_identity,
                    i.*,
                    IFNULL(ur.classname, 'user-rank-unranked') AS classname
                FROM
                    `API_Tokens` at
                INNER JOIN
                    `Identities` i ON i.user_id = at.user_id
                LEFT JOIN
                    User_Rank ur ON ur.user_id = i.user_id
                WHERE
                    at.token = ? AND i.username = ?
            ";
            $params[] = $apiToken;
            $params[] = $username;
        }
        $sql .= ';';

        /** @var list<array{apitoken_id: int, classname: string, country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, is_main_identity: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
        if (count($rs) === 1 && $username === null) {
            $apiTokenId = $rs[0]['apitoken_id'];
            unset($rs[0]['apitoken_id'], $rs[0]['is_main_identity']);
            return [
                'currentIdentity' => $rs[0],
                'loginIdentity' => $rs[0],
                'apiTokenId' => $apiTokenId,
            ];
        }
        if (count($rs) !== 2 || $username === null) {
            return null;
        }
        // Put the main user identity first.
        usort(
            $rs,
            /**
             * @param array{apitoken_id: int, classname: string, country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, is_main_identity: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string} $a
             * @param array{apitoken_id: int, classname: string, country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, is_main_identity: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string} $b
             */
            fn ($a, $b): int => $b['is_main_identity'] - $a['is_main_identity'],
        );
        $currentIdentity = array_pop($rs);
        $loginIdentity = array_pop($rs);
        $apiTokenId = $currentIdentity['apitoken_id'];
        unset(
            $currentIdentity['apitoken_id'],
            $currentIdentity['is_main_identity']
        );
        unset(
            $loginIdentity['apitoken_id'],
            $loginIdentity['is_main_identity']
        );
        return [
            'currentIdentity' => $currentIdentity,
            'loginIdentity' => $loginIdentity,
            'apiTokenId' => $apiTokenId,
        ];
    }

    /**
     * @return array{limit: int, remaining: int, reset: \OmegaUp\Timestamp}|null
     */
    public static function updateUsage(
        int $apiTokenId,
        \OmegaUp\Timestamp $now
    ) {
        try {
            \OmegaUp\DAO\DAO::transBegin();

            /** @var array{last_used: \OmegaUp\Timestamp, use_count: int}|null */
            $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow(
                '
                    SELECT
                        at.last_used,
                        at.use_count
                    FROM
                        `API_Tokens` at
                    WHERE
                        at.apitoken_id = ?
                    FOR UPDATE;
                ',
                [$apiTokenId],
            );
            if ($rs === null) {
                \OmegaUp\DAO\DAO::transRollback();
                return null;
            }

            $lastUsed = (new \DateTime('now', new \DateTimeZone('UTC')))
                ->setTimestamp($now->time);
            $lastUsed->setTime(intval($lastUsed->format('G')), 0);
            if ($lastUsed->getTimestamp() === $rs['last_used']->time) {
                $rs['use_count'] = min(
                    OMEGAUP_SESSION_API_HOURLY_LIMIT,
                    $rs['use_count'] + 1,
                );
            } else {
                $rs['last_used'] = new \OmegaUp\Timestamp(
                    $lastUsed->getTimestamp()
                );
                $rs['use_count'] = 1;
            }

            \OmegaUp\MySQLConnection::getInstance()->Execute(
                '
                    UPDATE
                        `API_Tokens`
                    SET
                        last_used = ?,
                        use_count = ?
                    WHERE
                        apitoken_id = ?;
                ',
                [$rs['last_used'], $rs['use_count'], $apiTokenId],
            );

            \OmegaUp\DAO\DAO::transEnd();

            return [
                'reset' => new \OmegaUp\Timestamp(
                    $lastUsed->add(new \DateInterval('PT1H'))->getTimestamp(),
                ),
                'limit' => OMEGAUP_SESSION_API_HOURLY_LIMIT,
                'remaining' => OMEGAUP_SESSION_API_HOURLY_LIMIT - $rs['use_count'],
            ];
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }
    }

    /**
     * @return list<array{name: string, timestamp: \OmegaUp\Timestamp, last_used: \OmegaUp\Timestamp, rate_limit: array{reset: \OmegaUp\Timestamp, limit: int, remaining: int}}>
     */
    public static function getAllByUser(int $userId) {
        /** @var list<array{last_used: \OmegaUp\Timestamp, name: string, timestamp: \OmegaUp\Timestamp, use_count: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            '
                SELECT
                    at.name,
                    at.timestamp,
                    at.last_used,
                    at.use_count
                FROM
                    `API_Tokens` at
                WHERE
                    at.user_id = ?
                ORDER BY
                    at.apitoken_id;
            ',
            [$userId],
        );

        $lastUsed = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->setTimestamp(\OmegaUp\Time::get());
        $lastUsed = $lastUsed->setTime(intval($lastUsed->format('G')), 0);
        $resetTimestamp = new \OmegaUp\Timestamp(
            $lastUsed->add(new \DateInterval('PT1H'))->getTimestamp(),
        );

        $apiTokens = [];
        foreach ($rs as &$apiToken) {
            $useCount = $apiToken['use_count'];
            unset($apiToken['use_count']);
            $remaining = OMEGAUP_SESSION_API_HOURLY_LIMIT;
            if ($lastUsed->getTimestamp() == $apiToken['last_used']->time) {
                $remaining = max(0, $remaining - $useCount);
            }
            $apiToken['rate_limit'] = [
                'reset' => $resetTimestamp,
                'limit' => OMEGAUP_SESSION_API_HOURLY_LIMIT,
                'remaining' => $remaining,
            ];
            $apiTokens[] = $apiToken;
        }

        return $apiTokens;
    }

    public static function getCountByUser(int $userId): int {
        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne(
            '
                SELECT
                    COUNT(*)
                FROM
                    `API_Tokens` at
                WHERE
                    at.user_id = ?;
            ',
            [$userId],
        );
    }

    public static function deleteByName(int $userId, string $name): void {
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            '
                DELETE FROM
                    `API_Tokens`
                WHERE
                    user_id = ? AND name = ?;
            ',
            [$userId, $name],
        );
        if (\OmegaUp\MySQLConnection::getInstance()->Affected_Rows() == 0) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
    }
}
