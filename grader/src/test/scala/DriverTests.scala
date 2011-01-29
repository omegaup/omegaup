import omegaup._
import omegaup.grader._
import omegaup.grader.drivers._

import Lenguaje._

import org.scalatest.FlatSpec
import org.scalatest.matchers.ShouldMatchers

class DriverSpec extends FlatSpec with ShouldMatchers {

	"TJUdriver" should "submit" in {
		TJU.start
		TJU !? Submission(-1, Lenguaje.C, 2231, """#include <stdio.h>

int numbers[20];

int N, S;

int superab(int k, int s) {
	if(k == N) {
		return s == S;
	}	
	if(superab(k+1, s)) return 1;
	if(superab(k+1, s + numbers[k])) return 1;
	
	return 0;
}

int main() {
	int ni;
	while(scanf("%d %d\n", &N, &S) && (N != 0 && S != 0)) {
		for(ni = 0; ni < N; ni++) scanf("%d", &numbers[ni]);
		
		if(superab(0, 0))
			printf("Yes\n");
		else
			printf("No\n");
	}
	
	return 0;
}""")
	}
}
