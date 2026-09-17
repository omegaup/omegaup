class program {
	define turnright() {
		turnleft();
		turnleft();
		turnleft();
	}

	define halfturn() {
		turnleft();
		turnleft();
	}

	program () {
		turnright();

		while (frontIsClear || leftIsClear) {
			if (notNextToABeeper()) {
				putbeeper();
			}
			if (frontIsClear) {
				move();
			} else {
				if (leftIsClear()) {
					turnleft();
					move();
					turnleft();
					while(frontIsClear) move();
					halfturn();
				} else {
					putbeeper();
					turnoff();
				}
			}
		}

		if (notNextToABeeper()) {
		  putbeeper();
		}
	}
}
