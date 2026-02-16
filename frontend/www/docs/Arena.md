Arena is a web service that the Frontend must call for everything related to contests (and a bit of their administration, such as problem modification, etc.). Arena will be part of the Frontend during v1, and will be a separate component starting from v2.

# General Guidelines

*Since we only have SSL on omegaup.com, the API will be under the path https://omegaup.com/api/
* Most API calls require an `auth_token` parameter, which can be obtained by calling `/api/user/login/` or by logging in through the normal web page.
* Session handling will be through `POST`, but cookies can also be used so that `GET` calls can authenticate.
* Parameters will be sent through *JSON*.
* The `auth_token` parameter with a valid token must be sent for all calls that require authentication.

# Errors

* All responses have a field called `status`.
when calls are successful, the value will be literal `ok`.
When there is error, the value of `status` will be `error` and there will be field called `error` containing a human-readable description of the error (in the account's configured language), an `errorcode` field, and an `errorname` field containing both a numeric and a text identifier for the error.

* Additionally, the server will set the HTTP status to the appropriate value in the following cases:


| Code | Response            | Description                                                                     |
| ------ | ----------------------- | -------------------------------------------------------------------------------- |
| 200    | OK                      | No  problems occurred and the request was successful.                               |
| 400    | BAD REQUEST             | The request (including the JSON message) is malformed                    |
| 401    | AUTHENTICATION REQUIRED | the request is missing the `auth_token` field, either via a cookie or in the json request. |
| 403    | FORBIDDEN               | The resource was found, but the user does not have the necessary privileges to access or modify it (e.g., a user trying to read other users’ runs, or enter the contest admin panel). |
| 404    | NOT FOUND               | The resource was not found (user, problem, contest, run, etc.), or is deliberately hidden (e.g., private contests).|
| 505    | INTERNAL SERVER ERROR   | The request ended unexpectedly. The response may even be empty, or the description ambiguous. Hopefully, there will be more information in the logs regarding this error.|

# Authentication

### POST `/api/user/login`
Send the username (or email) and password, receive an auth token. Auth tokens are valid for 24 hours and are strings of no more than 128 characters.

#### Parameters
  * `usernameOrEmail`The user’s username or email address in plain text. 
  * `password` The user’s password in plain text.

#### Returns
  * `auth_token` Token for this session.

# Contest
### GET `/api/contest/list`

List (by default the last 20 contests) that the user “can see.”

#### Parameters
Can receive different parameters depending on the contest list you want to view.
  * `active`: [`ACTIVE`, `FUTURE`, `PAST`] Indicates which contests to show. 
  * `recommended`: [`RECOMMENDED`, `NOT_RECOMMENDED`] Whether to show recommended contests.
  * `participating`: [`YES`, `NO`] Whether to show contests in which the user is participating.
  * `public`: [`YES`, `NO`] Whether to show public contests or ones the user is registered in.

#### Response
    {
        'number_of_results': int // Number of results shown
        'results': [
            {
                'contest_id' : int // Id del concurso
                'problemset_id' : int // Id del conjunto de problemas
                'alias': string // Alias del concurso, necesario para acceder
                'title': string // Título de cada concurso
                'description': string // Descripción de cada concurso
                'start_time': int // Hora de inicio en (timestamp) 
                'finish_time': int // Hora de terminación del concurso en (timestamp)
                'last_updated': int // Hora en que se modificó por última vez el concurso (timestamp)
                'original_finish_time': datetime // Hora de terminación del concurso
                'admission_mode': enum['public', 'private', 'registration'] // Indica si el concurso es público, privado o
                                                                            // requiere de registro por parte del usuario.
                'recommended': bool // Indica si el concurso es recomendado
                'duration': int // Indica el tiempo que estará disponible el concurso (muestra la diferencia entre la hora
                                // de inicio y la hora de terminación)
                'window_length': int // Indica el tiempo que estará disponible el concurso una vez que este sea abierto por 
                                     // el usuario (regresará `null` si el concurso no fue configurado con la característica)
            },
            ...
        ]
    }

### POST `/api/contest/create`
If the user has an `auth_token`, creates a new contest without associated problems.

#### Parameters

{

    "auth_token": string // User must be logged in
    "title": string // Contest title
    "description": string // A brief description of the contest's purpose
    "start_time": datetime // Contest start time
    "finish_time": datetime // Contest end time
    "window_length": int // Optional if each user has the same amount of time to complete the contest regardless of when they join
    "alias": string(32) // Alias required to access the contest
    "points_decay_factor": double (0,1)
    "submissions_gap": int (0, finish_time - start_time) // Minimum time in seconds a user must wait after submitting before making another submission
    "feedback": enum (no, yes, partial)
    "penalty": int (0, INF) // Integer indicating the number of minutes penalized for a non-accepted verdict
    "public": bool // Public contest or not. By default, contests are private and cannot be public until problems have been added
    "scoreboard": int (0,100) // Percentage of the contest time during which the scoreboard is visible
    "penalty_type": enum (none, problem_open, contest_start, runtime) // How the penalty is calculated for each submission
    "show_scoreboard_after": bool // Whether to display the full scoreboard after the contest ends
    "languages": set (kp, kj, c11-gcc, c11-clang, ...) // Languages allowed in the contest, multiple can be set separated by commas
    "basic_information": bool // Whether users must have registered basic information (Country, State, School) to join
    "requests_user_information": enum (no, optional, required) // Whether the organizer will request permission to view contestant information
    
}

---

### GET `/api/contest/publicdetails/`
If the user has permission, shows details of the contest `:contest_alias` (basic problem info, remaining time, mini-ranking... a simple, cacheable query).

#### Parameters
* `contest_alias`: string // Alias of the contest whose public details are requested

#### Output
{

    "alias": string // Contest alias
    "title": string // Contest title
    "description": string // Contest description
    "start_time": datetime // Start time
    "finish_time": datetime // End time
    "window_length": int // Time a user has to submit; NULL means the whole contest duration
    "scoreboard": int // 0–100, percentage of time the scoreboard is visible
    "points_decay_factor": int // Points decay factor (default 0 = no decay). TopCoder uses 0.7
    "partial_score": bool // True if user gets partial score for unsolved problems
    "sumbissions_gap": int // Minimum seconds a user must wait between submissions
    "feedback": enum (yes, no)
    "penalty": int // Minutes penalty for a non-accepted verdict
    "penalty_time_start": int // When penalty timing starts: at contest start or problem open
    "penalty_calc_policy": enum ("sum", "max")
    "admission_mode": enum (public, private, registration)
    
}

---

### GET `/api/problemset/scoreboard/`
If the user has permission, shows the full contest ranking for the given problem set ID.

#### Parameters
* `problemset_id`: int // Problem set ID
* `auth_token`: string (optional) // Session token

#### Output
{

    "problems": [
        {
            "order": int // For ordering problems
            "alias": string // For alphabetical ordering
        },
        ...
    ],
    "ranking": [
        {
            "username": string // Username
            "name": string // Display name
            "country": string // Country
            "classname": string // User rank title
            "is_invited": bool // Whether user was invited or joined a public contest
            "total": {
                "points": double // Total points
                "penalty": double // Total penalty
            }
            "problems": [
                {
                    "alias": string
                    "points": double // Points for this problem
                    "penalty": double // Penalty for this problem
                    "percent": int
                    "runs": int // Number of submissions for this problem
                },
                ...
            ]
        },
        ...
    ]
    
}

---

### GET `/api/problemset/scoreboardevents/`
If the user has permission, returns all events that caused someone's score to change.

#### Parameters
* `problemset_id`: int // Problem set ID
* `auth_token`: string (optional) // Session token

#### Output
{

    "events": [
        {
            "username": string // Username
            "name": string // Display name
            "delta": int // Seconds from contest start when event occurred
            "total": {
                "points": double // Total points
                "penalty": double // Total penalty
            }
            "problem": {
                "alias": string // Problem alias
                "points": double // Points for this problem
                "penalty": double // Penalty for this problem
            }
            "country": string // Country
            "classname": string // User rank title
            "is_invited": bool // Whether invited or joined public contest
        },
        ...
    ]
    
}

---

## Problems

### GET `/api/problem/details/`
If the user has permission, shows problem details and references to solutions they've submitted.

#### Parameters
* `problem_alias`: string // Alias of the problem

#### Output
{

    "title": string
    "alias": string
    "input_limit": int
    "validator": enum("remote","literal","token","token-caseless","token-numeric")
    "time_limit": int
    "memory_limit": int
    "visits": int
    "submissions": int
    "accepted": int
    "difficulty": double
    "creation_date": datetime
    "source": string // Author or original contest
    "order": enum("normal","inverse")
    "visibility": int
    "email_clarifications": bool
    "quality_seal": bool
    "version": string
    "commit": string
    "problemsetter": {
        "username": string
        "name": string
        "creation_date": int // Timestamp
    }
    "statement": {
        "language": string
        "images": []
        "markdown": string
    }
    "runs": [
        {
            "guid": string
            "language": enum("c","cpp","java","py","rb","pl","cs","p")
            "status": enum("new","waiting","compiling","running","ready")
            "veredict": enum("AC","PA","PE","WA","TLE","OLE","MLE","RTE","RFE","CE","JE")
            "runtime": int
            "memory": int
            "score": double
            "contest_score": double
            "ip": string
            "time": datetime
            "submit_delay": int // Minutes since problem opened until submission
        },
        ...
    ]
    "languages": [
        java, py2, py3, rb, ...
    ],
    "points": double
    "score": int
    "exists": bool
    "settings": {
        "cases": {
            "sample": {
                "in": string
                "out": string
                "weight": int
            },
            ...
        }
        "limits": {
            "ExtraWallTime": string
            "MemoryLimit": string
            "OutputLimit": string
            "OverallWallTimeLimit": string
            "TimeLimit": string
        }
        "validator": {
            "name": string
            "tolerance": string
        }
    }
    
}

---

### POST `/api/problem/create/`
If the user has a valid `auth_token`, creates a new problem which can later be associated to a contest or course.

#### Parameters
{

    "title": string
    "alias": string
    "source": string // Author or original contest
    $_FILES["problem_contents"]
    "validator": enum("remote","literal","token","token-caseless","token-numeric") // optional (default "token")
    "languages": enum("c11-clang,c11-gcc,cpp11-clang,cpp11-gcc,cpp17-clang,cpp17-gcc,cs,hs,java,lua,pas,py2,py3,rb", "kj,kp", "cat", "") // optional
    "validator_time_limit": int // optional (default 1000)
    "time_limit": int (ms) // optional (default 1000)
    "overall_wall_time_limit": int (ms) // optional (default 60000)
    "extra_wall_time": int (ms) // optional (default 0)
    "memory_limit": int (KiB) // optional (default 32768)
    "output_limit": int (bytes) // optional (default 10240)
    "input_limit": int (bytes) // optional (default 10240)
    "order": string enum("normal","inverse") // optional (default normal)
    "visibility": int // optional (default 0 - private)
    "tags": []
    
}

---

## Runs

### POST `/api/run/create/`
If logged in, the user submits a solution.

#### Parameters
* `auth_token`: string // Session token
* `problem_alias`: string // Problem alias
* `language`: string // Submission language
* `source`: string // Code source
* `contest_alias`: string (optional) // Contest alias if the problem is in a contest

#### Output
{
    "submission_deadline": int // Deadline to submit (timestamp). Zero if not in a contest
    "nextSubmissionTimestamp": int // When user can submit again (timestamp)
    "guid": string
}

---

### GET `/api/problem/runs/`
If the user has permission, returns the user's latest submissions for a specific problem.

#### Parameters
* `auth_token`: string // Session token
* `problem_alias`: string // Problem alias

#### Output
{

    "runs": [
        {
            "guid": string
            "language": string
            "status": string
            "verdict": enum("AC","PA","PE","WA","TLE","OLE","MLE","RTE","RFE","CE","JE")
            "runtime": int
            "penalty": int
            "memory": int
            "score": double
            "contest_score": double
            "time": int
            "submit_delay": int
            "alias": string
            "username": string
        },
        ...
    ]
    
}

---

### GET `/api/run/details/`
If the user has permission, shows the solution and its evaluation status.

#### Parameters
* `auth_token`: string
* `run_alias`: string

#### Output
{

    "admin": bool
    "guid": string
    "language": string
    "source": string
    "details": {
        "verdict": enum("AC","PA","PE","WA","TLE","OLE","MLE","RTE","RFE","CE","JE")
        "compile_meta": {
            "verdict": string
            "time": double
            "sys_time": double
            "wall_time": double
            "memory": int
        }
        "score": double
        "contest_score": double
        "max_score": double
        "time": double
        "wall_time": double
        "memory": int
        "judged_by": string
    }
    
}

---

## Clarifications

### POST `/api/clarification/create/`
If the user has permission, sends a clarification about a problem. Returns an ID to track it.

#### Parameters
{

    "auth_token": string
    "problem_alias": string
    "contest_alias": string (optional if not in a contest)
    "message": string
    
}

#### Output
{

    "clarification_id": int
    
}

---

### GET `/api/problem/clarifications/`
Returns all clarifications for a problem visible to the user.

#### Parameters
{

    "auth_token": string
    "problem_alias": string
    "offset": int (optional, default 0)
    "rowcount": int (optional, default 20)
    
}

#### Output
{

    "clarifications": [
        {
            "clarification_id": int
            "contest_alias": string
            "author": string
            "message": string
            "answer": null|string
            "time": int
            "public": bool
        },
        ...
    ]
    
}

---

### POST `/api/clarification/update/`
If the user created the problem or contest, they can respond to clarifications.

#### Parameters
{

    "auth_token": string
    "clarification_id": int
    "answer": string
    "public": bool
    
}

#### Output
{

    "status": string
    
}

---

### GET `/api/contest/clarifications/`
Returns all clarifications for a contest visible to the user.

#### Parameters
{

    "auth_token": string
    "contest_alias": string
    "offset": int (optional, default 0)
    "rowcount": int (optional, default 20)
    
}

#### Output
{

    "clarifications": [
        {
            "clarification_id": int
            "contest_alias": string
            "author": string
            "receiver": null|string
            "message": string
            "answer": null|string
            "time": int
            "public": bool
        },
        ...
    ]
    
}

---

## Returns HTML:

### GET `/arena/`
Returns the arena HTML.  
If not logged in, shows the list of current public contests.  
If logged in, shows the list of contests the user belongs to.

### GET `/arena/:contest_alias`
If not logged in, shows the intro with contest details and a login button.  
If logged in, shows the same intro with a "Start contest" button.

### GET `/arena/:contest_alias/scoreboard`
If user can view it, returns the HTML associated with the contest, visually arranging the `/api/problemset/scoreboard/` contents.
