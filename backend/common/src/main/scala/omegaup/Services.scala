package omegaup

import omegaup.data._
import java.io.InputStream

trait RunCaseCallback {
	def apply(filename: String, length: Long, stream: InputStream): Unit
}

abstract class RunnerService {
	def compile(message: CompileInputMessage): CompileOutputMessage
	def run(message: RunInputMessage, callback: RunCaseCallback): RunOutputMessage
	def input(inputName: String, inputStream: InputStream, size: Int = -1): InputOutputMessage
	def name(): String
	def port(): Int = 21681
	override def toString() = "RunnerService(%s)".format(name)
}

abstract class GraderService {
	def grade(id: Long): GradeOutputMessage
}

/* vim: set noexpandtab: */
