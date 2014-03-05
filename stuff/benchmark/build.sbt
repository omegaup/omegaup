name := "Benchmark"

version := "1.0"

organization := "omegaup"

scalaVersion := "2.10.3"

scalacOptions ++= Seq("-unchecked", "-deprecation", "-feature")

exportJars := true

packageOptions in (Compile, packageBin) +=
    Package.ManifestAttributes( java.util.jar.Attributes.Name.MAIN_CLASS -> "omegaup.tools.Benchmark" )

libraryDependencies ++= Seq(
	"org.json4s" %% "json4s-native" % "3.2.7"
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
  "-keepclassmembers class omegaup.* { *; }",
  "-keepclassmembers class omegaup.tools.* { *; }",
  ProguardOptions.keepMain("omegaup.tools.Benchmark")
)

ProguardKeys.inputFilter in Proguard := { file =>
  file.name match {
    case "benchmark_2.10-1.0.jar" => None
    case _ => Some("!**/ECLIPSEF.RSA,!**/ECLIPSEF.SF,!about.html,!META-INF/MANIFEST.MF,!rootdoc.txt")
  }
}

javaOptions in (Proguard, ProguardKeys.proguard) := Seq("-Xmx1G")
