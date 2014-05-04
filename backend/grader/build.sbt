name := "Grader"

version := "1.1"

organization := "omegaup"

scalaVersion := "2.10.3"

scalacOptions ++= Seq("-unchecked", "-deprecation", "-feature")

exportJars := true

packageOptions in (Compile, packageBin) +=
    Package.ManifestAttributes( java.util.jar.Attributes.Name.MAIN_CLASS -> "omegaup.Service" )

libraryDependencies ++= Seq(
  "org.eclipse.jetty" % "jetty-server" % "9.1.2.v20140210",
  "org.eclipse.jetty" % "jetty-client" % "9.1.2.v20140210",
  "org.eclipse.jetty" % "jetty-security" % "9.1.2.v20140210",
  "org.eclipse.jetty.websocket" % "websocket-server" % "9.1.2.v20140210",
  "net.liftweb" %% "lift-json" % "2.5.1",
  "org.slf4j" % "log4j-over-slf4j" % "1.7.6",
  "ch.qos.logback" % "logback-core" % "1.1.1",
  "ch.qos.logback" % "logback-classic" % "1.1.1",
  "commons-codec" % "commons-codec" % "1.9",
  "org.apache.commons" % "commons-compress" % "1.8",
  "org.scalatest" %% "scalatest" % "2.1.2" % "test",
  "com.h2database" % "h2" % "1.3.175" % "runtime",
  "mysql" % "mysql-connector-java" % "5.1.29" % "runtime"
)

proguardSettings 

ProguardKeys.options in Proguard ++= Seq(
  "-dontskipnonpubliclibraryclasses",
  "-dontskipnonpubliclibraryclassmembers",
  "-dontoptimize",
  "-dontobfuscate",
  "-dontpreverify",
  "-dontnote",
  "-dontwarn",
  "-keep interface scala.ScalaObject",
  "-keep class omegaup.*",
  "-keep class omegaup.data.*",
  "-keep class omegaup.grader.*",
  "-keep class omegaup.runner.*",
  "-keepclassmembers class omegaup.data.* { *; }",
  "-keepclassmembers class omegaup.runner.* { *; }",
  "-keepclassmembers class omegaup.Service { *; }",
  "-keep class scala.collection.JavaConversions",
  "-keep class org.eclipse.jetty.util.log.Slf4jLog",
  "-keep class org.eclipse.jetty.websocket.server.WebSocketServerFactory { *; }",
  "-keep class org.eclipse.jetty.websocket.api.extensions.*",
  "-keep class org.eclipse.jetty.websocket.common.extensions.**",
  "-keep class org.eclipse.jetty.websocket.**",
  "-keep class ch.qos.logback.classic.Logger",
  ProguardOptions.keepMain("omegaup.Service")
)

ProguardKeys.inputFilter in Proguard := { file =>
  file.name match {
    case "grader_2.10-1.1.jar" => None
    case _ => Some("!**/ECLIPSEF.RSA,!**/ECLIPSEF.SF,!about.html,!META-INF/MANIFEST.MF,!rootdoc.txt,!META-INF/services/java.sql.Driver,!META-INF/LICENSE.txt,!META-INF/NOTICE.txt")
  }
}

javaOptions in (Proguard, ProguardKeys.proguard) := Seq("-Xmx2G")
