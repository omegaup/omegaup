import net.liftweb.json._
import net.liftweb.json.Serialization.{read, write}

import org.scalatest.FlatSpec
import org.scalatest.matchers.ShouldMatchers

case class RandomObject(identifier: Int, name: String)

class JsonSpec extends FlatSpec with ShouldMatchers {

	"A random object" should "be automatically serializable" in {
		implicit val formats = Serialization.formats(NoTypeHints)
		
		val obj = new RandomObject(5, "Hello, World!")
		
		val json = write(obj)
		json should equal ("""{"identifier":5,"name":"Hello, World!"}""")
		obj should equal (read[RandomObject](json))
		
		evaluating { read[RandomObject]("""{"identifier":"bar"}""") } should produce [net.liftweb.json.MappingException]
		evaluating { read[RandomObject]("""{}""") } should produce [net.liftweb.json.MappingException]
		evaluating { read[RandomObject]("""/""") } should produce [net.liftweb.json.JsonParser.ParseException]
	}
	
}
