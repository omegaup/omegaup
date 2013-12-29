package omegaup

import omegaup.data._
import java.io.{File,InputStream}

abstract class RunnerService {
	def compile(message: CompileInputMessage): CompileOutputMessage
	def run(message: RunInputMessage, zipFile: File) : Option[RunOutputMessage]
	def input(inputName: String, inputStream: InputStream, size: Int = -1): InputOutputMessage
	def name(): String
}

abstract class GraderService {
	def grade(id: Long): GradeOutputMessage
}
