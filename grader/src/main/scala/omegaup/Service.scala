package omegaup

object Service extends Object with Log with Using {
	def main(args: Array[String]) = {
		// Setting keystore properties
		System.setProperty("javax.net.ssl.keyStore", Config.get("grader.keystore", "omegaup.jks"))
		System.setProperty("javax.net.ssl.trustStore", Config.get("grader.truststore", "omegaup.jks"))
		System.setProperty("javax.net.ssl.keyStorePassword", Config.get("grader.keystore.password", "omegaup"))
		System.setProperty("javax.net.ssl.trustStorePassword", Config.get("grader.truststore.password", "omegaup"))
		
		// logger
		Logging.init()

		val servers = List(omegaup.grader.Manager.init(), omegaup.broadcaster.Broadcaster.init())
		
		Runtime.getRuntime.addShutdownHook(new Thread() {
			override def run() = {
				info("Shutting down")

				servers foreach (_.stop)
			}
		});
		
		servers foreach (_.join)
	}
}
