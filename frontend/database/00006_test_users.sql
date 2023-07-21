INSERT INTO
  Users (username, name, password, verified)
VALUES
  ("omegaup", "omegaUp admin",
    "$2a$08$tyE7x/yxOZ1ltM7YAuFZ8OK/56c9Fsr/XDqgPe22IkOORY2kAAg2a", 1),
  ("user", "omegaUp user",
    "$2a$08$wxJh5voFPGuP8fUEthTSvutdb1OaWOa8ZCFQOuU/ZxcsOuHGw0Cqy", 1);

INSERT INTO
  Emails (email, user_id)
VALUES
  ("admin@omegaup.com", 1),
  ("user@omegaup.com", 2);

INSERT INTO
  User_Roles
VALUES
  (1, 1, 0);

UPDATE Users SET main_email_id=user_id;
