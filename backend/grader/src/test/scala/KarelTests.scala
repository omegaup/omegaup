import omegaup._

import org.scalatest.FlatSpec
import org.scalatest.matchers.ShouldMatchers

class KarelSpec extends FlatSpec with ShouldMatchers {
	"XmlWalker" should "match stuff" in {
		val walker = new XmlWalker("""<resultados><mundos><mundo nombre="divisores"><linea fila="3" compresionDeCeros="true">(4) 5 </linea><linea fila="2" compresionDeCeros="true">(5) 6 </linea></mundo></mundos><programas><programa nombre="divisores" resultadoEjecucion="FIN PROGRAMA"><karel x="1" y="1" direccion="NORTE"/></programa></programas></resultados>""")
		
		walker.get("mundo.linea.@fila=3") should equal ("(4) 5")
		walker.get("mundo.linea.@fila=2") should equal ("(5) 6")
		walker.get("karel.@x") should equal ("1")
		walker.get("karel.@y") should equal ("1")
		walker.get("karel.@direccion") should equal ("NORTE")
		
		walker.get("foo") should equal ("")
	}	
}
