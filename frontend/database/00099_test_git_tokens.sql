-- omegaup:omegaup
UPDATE
  Identities
SET
  password = '$argon2id$v=19$m=1024,t=2,p=1$7IYovf67Xtv0EAvZnKTIsQ$VgdbmwOXz9QtoM/tbx6pKyjbdrMEmotTDJE+NzRNyK0'
WHERE
  username = 'omegaup';
UPDATE
  Users
SET
  password = '$argon2id$v=19$m=1024,t=2,p=1$7IYovf67Xtv0EAvZnKTIsQ$VgdbmwOXz9QtoM/tbx6pKyjbdrMEmotTDJE+NzRNyK0',
  git_token = '$argon2id$v=19$m=1024,t=2,p=1$7IYovf67Xtv0EAvZnKTIsQ$VgdbmwOXz9QtoM/tbx6pKyjbdrMEmotTDJE+NzRNyK0'
WHERE
  username = 'omegaup';

-- user:user
UPDATE
  Identities
SET
  password = '$argon2id$v=19$m=1024,t=3,p=1$uSo7hIXamDD1j1Dd3gHrBw$FLALbWHc2eTG87iZhwfDJg32Q5pbUfjPfpLJFz/e8Rg'
WHERE
  username = 'user';
UPDATE
  Users
SET
  password = '$argon2id$v=19$m=1024,t=3,p=1$uSo7hIXamDD1j1Dd3gHrBw$FLALbWHc2eTG87iZhwfDJg32Q5pbUfjPfpLJFz/e8Rg',
  git_token = '$argon2id$v=19$m=1024,t=3,p=1$uSo7hIXamDD1j1Dd3gHrBw$FLALbWHc2eTG87iZhwfDJg32Q5pbUfjPfpLJFz/e8Rg'
WHERE
  username = 'user';
