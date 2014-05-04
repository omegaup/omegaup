package omegaup.runner

import omegaup._
import omegaup.data._
import java.io._
import org.apache.commons.compress.compressors.bzip2.BZip2CompressorInputStream
import net.liftweb.json._

class OmegaUpRunstreamReader(callback: RunCaseCallback) extends Object with Using with Log {
	class DebugInputStream(stream: InputStream) extends InputStream {
		val buffer = new StringBuilder

		private def add(c: Int) = {
			var b = c.toByte
			if (32 <= b && b < 128) {
				buffer.append(b.toChar)
			} else {
				buffer.append(f"\\x$b%02x")
			}
			c
		}

		override def available(): Int = stream.available
		override def close(): Unit = stream.close
		override def markSupported(): Boolean = false
		override def mark(readLimit: Int): Unit = {}
		override def skip(n: Long): Long = 0
		override def read(b: Array[Byte]): Int = read(b, 0, b.length)
		override def read(): Int = {
			val r = stream.read
			if (r >= 0) add(r)
			r
		}
		override def read(b: Array[Byte], off: Int, len: Int): Int = {
			val r = stream.read(b, off, len)
			var i = 0
			for (i <- off until off + r) {
				add(b(i))
			}
			r
		}

		override def toString() = buffer.toString
	}

	class ChunkInputStream(stream: InputStream, length: Int) extends InputStream {
		var remaining = length

		override def available(): Int = Math.min(remaining, stream.available)
		override def close(): Unit = {}
		override def markSupported(): Boolean = false
		override def mark(readLimit: Int): Unit = {}
		override def read(): Int = {
			if (remaining == 0) {
				-1
			}	else {
				remaining -= 1
				stream.read
			}
		}
		override def read(b: Array[Byte]): Int = {
			read(b, 0, b.length)
		}
		override def read(b: Array[Byte], off: Int, len: Int): Int = {
			if (remaining == 0) return -1
			val r = stream.read(b, off, Math.min(remaining, len))
			remaining -= r
			r
		}
		override def skip(n: Long): Long = {
			if (remaining == 0) return 0
			val r = stream.skip(Math.min(remaining, n))
			remaining -= r.toInt
			r
		}
	}

	def apply(inputStream: InputStream): RunOutputMessage = {
		var debugStream: DebugInputStream = null
		try {
			using (new BZip2CompressorInputStream(inputStream)) { bzip2 => {
				debugStream = new DebugInputStream(bzip2)
				val dis = new DataInputStream(debugStream)

				while (dis.readBoolean) {
					val filename = dis.readUTF
					val length = dis.readLong
					val chunk = new ChunkInputStream(dis, length.toInt)
					callback(filename, length, chunk)
					var remaining = chunk.remaining
					while (remaining > 0) {
						val s = dis.skip(chunk.remaining)
						if (s == 0) {
							throw new RuntimeException("Cannot read the rest of the file")
						}
						remaining -= s.toInt
					}
				}

				implicit val formats = Serialization.formats(NoTypeHints)
				Serialization.read[RunOutputMessage](new InputStreamReader(dis))
			}}
		} finally {
			debug("Stream read: \"{}\"", debugStream.toString)
		}
	}
}

class RunnerProxy(val hostname: String, port: Int) extends RunnerService with Log {
	private val url = "https://" + hostname + ":" + port

	def name() = hostname

	override def port() = port

	override def toString() = "RunnerProxy(%s:%d)".format(hostname, port)

	def compile(message: CompileInputMessage): CompileOutputMessage = {
		Https.send[CompileOutputMessage, CompileInputMessage](url + "/compile/",
			message
		)
	}

	def run(message: RunInputMessage, callback: RunCaseCallback) : RunOutputMessage = {
		val reader = new OmegaUpRunstreamReader(callback)
		Https.send[RunOutputMessage, RunInputMessage](url + "/run/", message, reader.apply _)
	}
	
	def input(inputName: String, inputStream: InputStream, size: Int = -1): InputOutputMessage = {
		Https.zip_send[InputOutputMessage](url + "/input/", inputStream, size, inputName)
	}
	
	override def hashCode() = 28227 + 97 * hostname.hashCode + port
	override def equals(other: Any) = other match {
		case x:RunnerProxy => hostname == x.hostname && port == x.port
		case _ => false
	}
}

/* vim: set noexpandtab: */
