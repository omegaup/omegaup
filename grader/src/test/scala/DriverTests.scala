import omegaup._
import omegaup.data._
import omegaup.grader._
import omegaup.grader.drivers._

import Language._

import org.scalatest.{FlatSpec, BeforeAndAfterAll}
import org.scalatest.matchers.ShouldMatchers

class DriverSpec extends FlatSpec with ShouldMatchers with BeforeAndAfterAll {
	override def beforeAll() {
		import java.io._
		import java.util.zip._
		
		val root = new File("test-env")
                if (root.exists()) {
                  FileUtil.deleteDirectory(root)
                }
		root.mkdir()
		
		// populate temp database for problems and contests
		Config.set("db.driver", "org.h2.Driver")
		//Config.set("db.url", "jdbc:h2:mem:")
		Config.set("db.url", "jdbc:h2:file:" + root.getCanonicalPath + "/omegaup")
		Config.set("db.user", "sa")
		Config.set("db.password", "")

                Config.set("runner.sandbox.path", new File("../sandbox").getCanonicalPath)
		Config.set("submissions.root", root.getCanonicalPath + "/submissions")
		Config.set("grader.root", root.getCanonicalPath + "/grader")
		Config.set("problems.root", root.getCanonicalPath + "/problems")
		Config.set("compile.root", root.getCanonicalPath + "/compile")
		Config.set("input.root", root.getCanonicalPath + "/input")
		Config.set("runner.preserve", "true")
		Config.set("logging.level", "debug")
		Config.set("logging.file", "")
		Config.set("grader.port", "21681")

		Logging.init
		
		val input = new ZipInputStream(new FileInputStream("src/test/resources/omegaup-base.zip"))
		var entry: ZipEntry = input.getNextEntry
		val buffer = Array.ofDim[Byte](1024)
		var read: Int = 0
		
		while(entry != null) {
			val outFile = new File(root.getCanonicalPath + "/" + entry.getName)
			
			if(entry.getName.endsWith("/")) {
				outFile.mkdirs()
			} else {
				val output = new FileOutputStream(outFile)
				while( { read = input.read(buffer); read > 0 } ) {
					output.write(buffer, 0, read)
				}
				output.close
			}

			input.closeEntry
			entry = input.getNextEntry
		}
		
		input.close
		
		implicit val conn = Manager.connection
		
		FileUtil.read("src/main/resources/h2.sql").split("\n\n").foreach { Database.execute(_) }
		FileUtil.read("src/test/resources/h2.sql").split("\n\n").foreach { Database.execute(_) }
	}
	
	"OmegaUpDriver" should "submit" in {
		System.setProperty("grader.embedded_runner.enable", "true")
		
		val t = new Thread() { override def run(): Unit = { Manager.main(Array.ofDim[String](0)) } } 
		t.start
		
		val omegaUpSubmitContest = (id: Long, language: Language, code: String, user: Int, contest: Int, date: String) => {
			import java.util.Date
			import java.sql.Timestamp
			import java.text.SimpleDateFormat
			
			val file = java.io.File.createTempFile(System.currentTimeMillis.toString, "", new java.io.File(Config.get("submissions.root", ".")))
			
			implicit val conn = Manager.connection
			
			FileUtil.write(file.getCanonicalPath, code)
		
			Manager.grade(
				GraderData.insert(new Run(
					guid = file.getName,
					user = user,
					language = language,
					problem = new Problem(id = id),
					contest = contest match {
						case 0 => None
						case x: Int => Some(new Contest(id = x))
					},
					time = new Timestamp(date match {
						case null => new Date().getTime()
						case x: String => new SimpleDateFormat("yyyy-MM-dd HH:mm:ss").parse(x).getTime()
					})
				)).id
			)
		}
		
		val omegaUpSubmit = (id: Long, language: Language, code: String) =>
			omegaUpSubmitContest(id, language, code, 1, 0, null)
		
		omegaUpSubmit(1, Language.Cpp, """
			int main() {
				while(true);
			}
		""")
		
		omegaUpSubmitContest(1, Language.Cpp, """
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
		""", 1, 1, "2000-01-01 00:10:00")
		
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

		omegaUpSubmit(2, Language.Cpp, """
			#include <cstdlib>
			#include <iostream>
			#include <map>
			#include <unistd.h>

			using namespace std;

			int main(int argc, char *argv[]) {
				int a, b;
				cin >> a >> b;
				cout << a + b << endl;
				
				return EXIT_SUCCESS;
			}
		""")

		omegaUpSubmit(2, Language.Cpp, """
			#include <cstdlib>
			#include <iostream>
			#include <map>
			#include <unistd.h>

			using namespace std;

			int main(int argc, char *argv[]) {
				int a, b;
				cin >> a >> b;
				cout << 3 << endl;
				
				return EXIT_SUCCESS;
			}
		""")

		omegaUpSubmit(3, Language.Cpp, """
			#include <cstdlib>
			#include <iostream>
			#include <map>
			#include <unistd.h>

			using namespace std;

			int main(int argc, char *argv[]) {
				int a, b;
				cin >> a >> b;
				cout << a + b << endl;
				
				return EXIT_SUCCESS;
			}
		""")

		omegaUpSubmit(3, Language.Cpp, """
			#include <cstdlib>
			#include <iostream>
			#include <map>
			#include <unistd.h>

			using namespace std;

			int main(int argc, char *argv[]) {
				int a, b;
				cin >> a >> b;
				cout << 3 << endl;
				
				return EXIT_SUCCESS;
			}
		""")

		omegaUpSubmit(4, Language.Cpp, """
			#include <cstdlib>
			#include <iostream>
			#include <map>
			#include <unistd.h>

			using namespace std;

			int main(int argc, char *argv[]) {
				double a, b;
				cin >> a >> b;
				cout << a + b << endl;
				
				return EXIT_SUCCESS;
			}
		""")

		omegaUpSubmit(4, Language.Cpp, """
			#include <cstdlib>
			#include <iostream>
			#include <map>
			#include <unistd.h>

			using namespace std;

			int main(int argc, char *argv[]) {
				double a, b;
				cin >> a >> b;
				cout << a*a + b*b << endl;
				
				return EXIT_SUCCESS;
			}
		""")

		omegaUpSubmit(5, Language.KarelJava, """
class program {

void turn(n) { iterate(n) turnleft(); }

void avanza()
{
	

   if (frontIsClear)
   {
      move();
   }
   else
   {
       turn(2);
       while(frontIsClear)
          move();
       turn(3);
       if (frontIsClear)
       {
          move();
          turn(3);
       }
   }
}

void recur(n)
{
   if (notFacingNorth)
   {
     recuerdame(n);
     regresa();
   }
   else
   {
     turn(3);
     while(frontIsClear)
     {
         move();
     }
     turn(2);
   }
}

void regresa()
{
  if(frontIsClear)
  {
     move();
  }
  else
  {
     turn(1);
     move();
     turnleft();
     while(frontIsClear)
       move();
     turn(2);
  }
}

void crece(n)
{
   if(!iszero(n))
   {
      iterate(4)
      {
         if (frontIsClear)
         {
             move();
             if (notNextToABeeper)
             {
                putbeeper();
             }
             crece(pred(n));
             turn(2);
             move();
             turn(2);
         }
         turnleft();
      }
   }
}

void recuerdame(n)
{
   if (nextToABeeper)
   {
      avanza();
      recur(n);
      crece(n);
   }
   else
   {
      avanza();
      recur(n);
   }
}

void cuenta(n)
{
   if (nextToABeeper)
   {
      pickbeeper();
      cuenta(succ(n));
   }
   else
   {
      while(notFacingEast)
         turnleft();
      recuerdame(n);
   }
}

void recoge()
{
    if (notFacingNorth)
    {
       avanza();
       if (nextToABeeper)
       {
          pickbeeper();
          recoge();
          putbeeper();
       }
       else
       {
          recoge();
       }
    }else
    {
       turn(2);
       while(frontIsClear)
          move();
    }
}

program() {
    cuenta(0);
    while(notFacingEast)
       turnleft();
    recoge();
    turnoff();
}

}
		""")
	
		try { Thread.sleep(30000) }
		
		implicit val conn = Manager.connection
		
		var run: Run = GraderData.run(1).get
		run.status should equal (Status.Ready)
		run.veredict should equal (Veredict.TimeLimitExceeded)
		run.score should equal (0)
		run.contest_score should equal (0)
		
		run = GraderData.run(2).get
		run.status should equal (Status.Ready)
		run.veredict should equal (Veredict.Accepted)
		run.score should equal (1)
		run.contest_score should equal (100)
		
		run = GraderData.run(3).get
		run.status should equal (Status.Ready)
		run.veredict should equal (Veredict.PartialAccepted)
		run.score should equal (0.5)
		run.contest_score should equal (0)

		run = GraderData.run(4).get
		run.status should equal (Status.Ready)
		run.veredict should equal (Veredict.Accepted)
		run.score should equal (1)
		run.contest_score should equal (0)

		run = GraderData.run(5).get
		run.status should equal (Status.Ready)
		run.veredict should equal (Veredict.PartialAccepted)
		run.score should be (0.2 plusOrMinus 0.001)
		run.contest_score should equal (0)

		run = GraderData.run(6).get
		run.status should equal (Status.Ready)
		run.veredict should equal (Veredict.Accepted)
		run.score should equal (1)
		run.contest_score should equal (0)

		run = GraderData.run(7).get
		run.status should equal (Status.Ready)
		run.veredict should equal (Veredict.PartialAccepted)
		run.score should be (0.05 plusOrMinus 0.001)
		run.contest_score should equal (0)

		run = GraderData.run(8).get
		run.status should equal (Status.Ready)
		run.veredict should equal (Veredict.PartialAccepted)
		run.score should be (0.71 plusOrMinus 0.01)
		run.contest_score should equal (0)

		run = GraderData.run(9).get
		run.status should equal (Status.Ready)
		run.veredict should equal (Veredict.Accepted)
		run.score should equal (1)
		run.contest_score should equal (0)

		run = GraderData.run(10).get
		run.status should equal (Status.Ready)
		run.veredict should equal (Veredict.Accepted)
		run.score should equal (1)
		run.contest_score should equal (0)
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
