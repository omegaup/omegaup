package openjuan

import java.util.Properties
import net.liftweb.json._

object Config {
	val props = new Properties()
	props.load(new java.io.FileInputStream("openjuan.properties"))
	
	def get(name: String): Option[String] = {
		props.getProperty(name) match {
			case null => None
			case x: String => Some(x)
		}
	}
	
	def get(name: String, default: String): String = {
		props.getProperty(name) match {
			case null => default
			case x: String => x.toString
		}
	}
}
