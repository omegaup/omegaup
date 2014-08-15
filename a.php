<?php
for ($i = 0; $i < 2; $i++) {

	// Add private in the first pass, public in the second
	try {
		$problem_mask = NULL;
		if ($i === 0 && !is_null($r["current_user_id"])) {
			if (Authorization::IsSystemAdmin($r["current_user_id"])) {
				$problem_mask = new Problems(array(
						"public" => "0"
					));
			} else {
				// Sys admin can see al private problems
				$problem_mask = new Problems(array(
						"public" => "0",
						"author_id" => $r["current_user_id"]
					));
			}
		} else if ($i === 1) {
			$problem_mask = new Problems(array(
						"public" => 1
					));
		}

		if (!is_null($problem_mask)) {
			$problems = ProblemsDAO::search(
					$problem_mask, 
					"problem_id", 
					'DESC', 
					$r["offset"], 
					$r["rowcount"],
					is_null($r["query"]) ? 
						null : 
						array(
							"title" => $r["query"]
						)
				);
			
			foreach ($problems as $problem) {
				array_push($response["results"], $problem->asArray());
			}
		}
	} catch (Exception $e) {
		throw new InvalidDatabaseOperationException($e);
	}
}

?>
