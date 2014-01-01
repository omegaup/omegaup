package omegaup

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
				System.setOut(new java.io.PrintStream(new java.io.FileOutputStream(args(i))))
			}
			i += 1
		}

		// Setting keystore properties
		System.setProperty("javax.net.ssl.keyStore", Config.get("grader.keystore", "omegaup.jks"))
		System.setProperty("javax.net.ssl.trustStore", Config.get("grader.truststore", "omegaup.jks"))
		System.setProperty("javax.net.ssl.keyStorePassword", Config.get("grader.keystore.password", "omegaup"))
		System.setProperty("javax.net.ssl.trustStorePassword", Config.get("grader.truststore.password", "omegaup"))

		// logger
		Logging.init

		val servers = List(omegaup.grader.Manager.init(configPath), omegaup.broadcaster.Broadcaster.init)
		
		Runtime.getRuntime.addShutdownHook(new Thread() {
			override def run() = {
				info("Shutting down")

				servers foreach (_.stop)
			}
		});
		
		servers foreach (_.join)
	}
}
