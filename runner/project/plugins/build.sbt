<<<<<<< HEAD
resolvers += "Proguard plugin repo" at "http://siasia.github.com/maven2"

addSbtPlugin("com.github.siasia" % "xsbt-proguard-plugin" % "0.1")
=======
libraryDependencies <+= sbtVersion(v => "com.github.siasia" %% "xsbt-proguard-plugin" % (v+"-0.1.1"))
>>>>>>> a5293d946d50d6553556c81028248397d595a05a
