import omegaup._
import omegaup.data._
import omegaup.grader._
import omegaup.grader.drivers._

import Language._

import org.scalatest.FlatSpec
import org.scalatest.matchers.ShouldMatchers

class DriverSpec extends FlatSpec with ShouldMatchers {
	"OmegaUpDriver" should "submit" in {
		System.setProperty("grader.embedded_runner.enable", "true")
		
		val t = new Thread() { override def run(): Unit = { Manager.main(Array.ofDim[String](0)) } } 
		t.start
		
		try { Thread.sleep(1000) }
		
		OmegaUp.start
		
		val omegaUpSubmit = (id: Long, language: Language, code: String) => {
			val file = java.io.File.createTempFile(System.currentTimeMillis.toString, "", new java.io.File(Config.get("submissions.root", ".")))
			
			implicit val conn = Manager.connection
			
			FileUtil.write(file.getCanonicalPath, code)
		
			Manager.grade(
				GraderData.insert(new Run(
					guid = file.getName,
					language = language,
					problem = new Problem(id = id)
				)).id
			)
		}
		
		omegaUpSubmit(1, Language.Cpp, """
			int main() {
				while(true);
			}
		""")
		
		omegaUpSubmit(1, Language.Cpp, """
			#include <cstdlib>
			#include <iostream>
			#include <map>
			#include <unistd.h>

			using namespace std;

			int main(int argc, char *argv[]) {
				int a, b;
				cin >> a >> b;
				cout << "Hello, World!" << endl;
				cout << a + b << endl;
				
				return EXIT_SUCCESS;
			}
		""")
		
		omegaUpSubmit(1, Language.Cpp, """
			#include <cstdlib>
			#include <iostream>
			#include <map>
			#include <unistd.h>

			using namespace std;

			int main(int argc, char *argv[]) {
				int a, b;
				cin >> a >> b;
				cout << "Hello, World!" << endl;
				cout << 3 << endl;
				
				return EXIT_SUCCESS;
			}
		""")
		
		omegaUpSubmit(1, Language.Java, """
			class Main {
			public static void main(String[] args) {
			  double d = 2.2250738585072012e-308;
			  System.out.println("Value: " + d);
			 }
			}
		""")
		
		omegaUpSubmit(1, Language.Java, """
			class Main {
			public static void main(String[] args) {
			  System.out.println("Test:");
			  double d = Double.parseDouble("2.2250738585072012e-308");
			  System.out.println("Value: " + d);
			 }
			}
		""")
		
		t.join
	}
	
	/*
	"UVaDriver" should "login" in {
		UVa.start
		UVa ! Login
		UVa ! Submission(-1, Language.Cpp, 136, """
			int main() {
				while(true);
			}
		""")
		UVa ! Submission(-1, Language.Cpp, 136, """
			#include <cstdlib>
			#include <iostream>
			#include <map>
			#include <unistd.h>

			using namespace std;

			int main(int argc, char *argv[]) {
				cout << "The 1500'th ugly number is 859963392." << endl;
				
				return EXIT_SUCCESS;
			}
		""")
		UVa ! Submission(-1, Language.Cpp, 136, """
			int main() {
				while(true);
			}
		""")
		
		java.lang.System.in.read()
	}
	
	"LiveArchiveDriver" should "submit" in {
		LiveArchive.start
		LiveArchive ! Submission(-1, Language.Cpp, 4212, """
			int main() {
				while(true);
			}
		""")
		LiveArchive ! Submission(-1, Language.Cpp, 4212, """
			#include <cstdio>
			#include <algorithm>
			#include <cstring>

			using namespace std;

			int DPr[200000];
			int DPc[200000];
			int M, N, x;

			int main() {
				DPc[0] = DPc[1] = 0;
				DPr[1] = DPr[1] = 0;
				while(fscanf(stdin, "%d %d\n", &M, &N) && M && N) {
					for(int i = 0; i < M; i++) {
						for(int j = 0; j < N; j++) {
							fscanf(stdin, "%d", &x);
							DPr[j+2] = max(DPr[j+1], DPr[j] + x);
						}
						DPc[i+2] = max(DPc[i+1], DPc[i] + DPr[N+1]);
					}
					printf("%d\n", DPc[M+1]);
				}
			}
		""")
		LiveArchive ! Submission(-1, Language.Cpp, 4212, """
			int main() {
				while(true);
			}
		""")
		java.lang.System.in.read()
	}
	
	"TJUdriver" should "submit" in {
		TJU.start
		TJU ! Submission(-1, Language.C, 2231, """
			int main() {
				while(true);
			}
		""")
		TJU ! Submission(-1, Language.C, 2231, """
			#include <stdio.h>

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
			}
		""")
		TJU ! Submission(-1, Language.C, 2231, """
			int main() {
				while(true);
			}
		""")
		java.lang.System.in.read()
	}
	*/
}
