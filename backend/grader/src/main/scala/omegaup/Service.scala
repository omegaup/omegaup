package omegaup

trait ServiceInterface {
	def stop(): Unit
	def join(): Unit
}

object Service extends Object with Log with Using {
	def main(args: Array[String]) = {		
		// Parse command-line options.
		var configPath = "omegaup.conf"
		var i = 0
		while (i < args.length) {
			if (args(i) == "--config" && i + 1 < args.length) {
				i += 1
				configPath = args(i)
				Config.load(configPath)
			} else if (args(i) == "--output" && i + 1 < args.length) {
				i += 1
				var logStream = new java.io.PrintStream(new java.io.FileOutputStream(args(i), true))
				System.setOut(logStream)
				System.setErr(logStream)
			}
			i += 1
		}

		// logger
		Logging.init

		val servers = List(omegaup.broadcaster.Broadcaster.init, omegaup.grader.Manager.init(configPath))
		
		Runtime.getRuntime.addShutdownHook(new Thread() {
			override def run() = {
				info("Shutting down")
				try {
					servers foreach (_.stop)
				} catch {
					case e: Exception => {
						error("Error shutting down. Good night.", e)
					}
				}
			}
		});
		
		servers foreach (_.join)
		info("Shut down cleanly")
	}
}

/* vim: set noexpandtab: */
