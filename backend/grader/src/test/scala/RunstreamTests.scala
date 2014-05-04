import omegaup.grader._
import omegaup.runner._
import omegaup.data._
import omegaup._

import java.io._
import org.scalatest._
import org.scalatest.matchers._
import Matchers._

class RunstreamSpec extends FlatSpec with Using {

	"Runstream proxy" should "pass stuff correctly" in {
    val cases = List(
      ("foo", Array[Byte]()),
      ("bar", Array[Byte](0, 1, 2, 3))
    )
    val message = new RunOutputMessage(status = "ok", error = Some("surprise!"))

    // Write
    val output = new ByteArrayOutputStream
    using (new OmegaUpRunstreamWriter(output)) { writer => {
      cases.foreach { case (name, data)=> writer(name, data.length, new ByteArrayInputStream(data)) }
      writer.finalize(message)
    }}
    new PrintWriter(new OutputStreamWriter(output)).printf("Hello, World!")

    // Read
    var timesCalled = 0
    object TestRunCallback extends Object with RunCaseCallback {
      def apply(filename: String, length: Long, stream: InputStream): Unit = {
        timesCalled should be <(cases.length)
        filename should equal(cases(timesCalled)._1)
        length should equal(cases(timesCalled)._2.length)
        val buffer = new Array[Byte](length.toInt)
        stream.read(buffer)
        buffer should equal(cases(timesCalled)._2)
        timesCalled += 1
      }
    }
    val reader = new OmegaUpRunstreamReader(TestRunCallback)
    val input = new ByteArrayInputStream(output.toByteArray)
    reader(input) should equal(message)
    timesCalled should equal(cases.length)
	}
	
}

