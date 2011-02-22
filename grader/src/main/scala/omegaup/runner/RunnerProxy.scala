package omegaup.runner

import omegaup._
import omegaup.data._
import java.io._

class RunnerProxy(val hostname: String, val port: Int) extends RunnerService with Log {
	private val url = "https://" + hostname + ":" + port
	
	def compile(message: CompileInputMessage): CompileOutputMessage = {
		Https.send[CompileOutputMessage, CompileInputMessage](url + "/compile/",
			message
		)
	}
	
	def run(message: RunInputMessage, zipFile: File) : Option[RunOutputMessage] = {
		Https.receive_zip[RunOutputMessage, RunInputMessage](url + "/run/", message, zipFile.getCanonicalPath)
	}
	
	def input(inputName: String, inputStream: InputStream, size: Int = -1): InputOutputMessage = {
		Https.zip_send[InputOutputMessage](url + "/input/", inputStream, size, inputName)
	}
}
