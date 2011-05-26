import java.io.*;
import java.sql.*;




class Compilador {

	private String fileName;
	private String LANG;
	final private boolean imprimirSalida = true;

	public boolean compilar(){

		String comando = "";

		//no hay necesidad de compilar a python
		if(LANG.equals("Python")) 
			return true;

		//no hay necesidad de compilar perl
		if(LANG.equals("Perl")) 
			return true;

		//genera el comando ke se ejecutara
		if(LANG.equals("JAVA")) 
			comando = "javac " + fileName;


		if(LANG.equals("C")){
			 String [] test = fileName.split("/");
			 comando = "./compileC " + test[2] + " " + test[3]; //comando = "gcc " + fileName;
		}

		if(LANG.equals("C++")){
			 String [] test = fileName.split("/");
			 comando = "./compileC++ " + test[2] + " " + test[3]; 
		}

		System.out.println("Comando para compilar > " + comando);
		int exitVal = -1;

		//intentar compilar
		try{
			Process proc = Runtime.getRuntime().exec(comando);

			//esperar hasta que termine el proceso
			exitVal = proc.waitFor();

			//si es que vamos a imprimir salida
			if(imprimirSalida){
		
				//capturar la salida
				InputStreamReader isr = new InputStreamReader(proc.getInputStream());
				BufferedReader br = new BufferedReader(isr);
				
				String linea = "";
				while((linea = br.readLine()) != null){
					//imprimir en salida estandar
					System.out.println( linea );
				}

				//leer salida de error
				InputStreamReader isr2 = new InputStreamReader( proc.getErrorStream() );
				BufferedReader br2 = new BufferedReader( isr2 );
				
				
				
				String linea2 = null;
				String endString = "";
				
				while((linea2 = br2.readLine()) != null){

					System.out.println( ">" + linea2 );
					endString += linea2 + "\n";
				}
				
				if(endString.length() > 0){
					PrintWriter pw = new PrintWriter( new FileWriter( "../codigos/" + fileName.split("/")[2]  + ".compiler_out") );
					pw.println( endString );
					pw.flush();
					pw.close();					
				}


			}

		}catch(Exception e){
			//error interno del juez
			System.out.println("ERROR EN EL JUEZ: " + e);
			return false;
		}
		

		//si pudo compilar el juez
		//depende lo que regrese el compilador es si si compilo o no compilo
		return (exitVal == 0);
	}

	//constructor
	Compilador( ){
		System.out.println("Creando compilador...");
	}

	void setLang( String LANG ){
		System.out.println("Setting language..." + LANG);		
		this.LANG = LANG;
	}

	void setFile( String fileName ){
		System.out.println("Setting filename..." + fileName);		
		this.fileName = fileName;
	}

}





class Ejecutar implements Runnable{
	
	private String execID;
	private String LANG;
	final private boolean imprimirSalida = true;
	public String status = "TIME";	
	private Process proc;
	private String PID;
	private String comando;
	private String killcomand;

	public void destroyProc(){
		proc.destroy();
		destroyPID();
	}

	public void destroyPID(){

		//destruid pid con kill
		try{
			proc = Runtime.getRuntime().exec("./killprocess " + killcomand);
		}catch(IOException ioe){
		
		}

		//System.out.println("./killprocess " + killcomand);//java Main USER_CODE 0.3451665593858829
	}

	public void run(){
		synchronized(this){

			//ubicacion de el script sh externo
			comando = "";

			double uid = Math.random();

			//genera el comando ke se ejecutara si es java
			if(LANG.equals("Python")) {
				comando = "./runPython " + execID + " " + uid;
				killcomand = "python Main.py USER_CODE " + uid;
			}

			//genera el comando ke se ejecutara si es java
			if(LANG.equals("JAVA")){
				comando = "./runJava " + execID + " " + uid ;
				killcomand = "java Main USER_CODE " + uid;
			}

			//genera el comando ke se ejecutara si es perl
			if(LANG.equals("Perl")){
				comando = "./runPerl " + execID + " " + uid ;
				killcomand = "perl Main.pl USER_CODE " + uid;
			}

			//si es C
			if(LANG.equals("C")){
				comando = "./runC " + execID  + " " + uid; 
				killcomand = "a.out USER_CODE " + uid;
			}

			//si es C++
			if(LANG.equals("C++")){
				comando = "./runC " + execID  + " " + Math.random(); 
				killcomand = "a.out USER_CODE " + uid;
			}

			int exitVal = 0;

			PID = comando;

			try{
				//ejecutar el script
				proc = Runtime.getRuntime().exec(comando);

				//imprmir salida 
				if(imprimirSalida){

					//leer salida estandar
					InputStreamReader isr = new InputStreamReader( proc.getInputStream() );
					BufferedReader br = new BufferedReader(isr);
					boolean impMsg = true;
					String linea = null;
					while((linea = br.readLine()) != null){
						if(impMsg){
							System.out.println("Impresiones de tu programa a salida estandar:");
							impMsg = false;
						}
						System.out.println( linea );
					}

					//leer salida de error
					InputStreamReader isr2 = new InputStreamReader( proc.getErrorStream() );
					BufferedReader br2 = new BufferedReader( isr2 );
					impMsg = true;
					String linea2 = null;
					while((linea2 = br2.readLine()) != null){
						if(impMsg){
							System.out.println("Impresiones de tu programa a salida de error:");
							impMsg = false;
						}
						System.out.println( linea2 );
					}				
				}

				//esperar a que termine el proceso
				exitVal = proc.waitFor();
				
			} catch( Exception e ) {

				//error interno del juez
				//status = "ERROR_JUEZ";
				//System.out.println("Error, el juez no ha podido ejecutar el programa. \n" + e);
				//return;
			}

			//alguna exception del progrma invitado
			if( exitVal != 0 ) { 
				System.out.println(exitVal);
				status = "EXCEPTION";
				return; 
			}

			//avisar al otro hilo que hemos terminado
			status = "OK";
			notify();
		}
	}//run code thread

	void setLang( String lang ){
		LANG = lang;
	}

	//constructor
	Ejecutar(String s){
		this.execID = s;
	}

}//clase ejecutar



/* -------------------------------------------------------------------------------------------- *
* Juez - Esta es la primera que llama el PHP
*	Recibe 3 argumentos, usuario, probID, nombre del archivo, cliente IP
*	Al llegar a este punto, el usuario ya esta validado.
* -------------------------------------------------------------------------------------------- */
class Juez {

	//fields
	//conexion a la base de datos
	static private Conexion con;
	private static boolean DEBUG;
	static String LANG;

	public static void main(String [] args){

		//crear conexion con base
		try{
			con = new Conexion();
		}catch(Exception e){
			System.out.println("Error al crear la conexion con la base de datos.");
			return;
		}

		boolean debug = false;
		
		for(String foobar : args)
			if( foobar.equals("DEBUG") )
				debug = true;
			
		DEBUG = debug;

		System.out.println( "Total meoria de la maquina virutal : " +  (Runtime.getRuntime().totalMemory() / 1024) + "kb");
		
		if(DEBUG) System.out.println("MODO DEBUG ACTIVADO");


		String execID;
		String userID;
		String probID;
		String concursoID;


		//leer la base de datos y revisar si hay runs en waiting...
		ResultSet rs = con.query( "SELECT * FROM Ejecucion WHERE status = 'WAITING' LIMIT 1;" );

		try{
			if(!rs.next()) {
				System.out.println("No hay runs en espera...");
				return;
			}


			System.out.println("There is a run on wait : " + rs.getString("execID") );

			execID   = 	rs.getString("execID");
			LANG     = 	rs.getString("LANG");
			userID   = 	rs.getString("userID");
			probID 	= 	rs.getString("probID");
			concursoID = 	rs.getString("Concurso");

		}catch(SQLException sqle){

			System.out.println("Error al contactar la BD.");
			return;
		}


		//ponerlo como que estoy jueseandooo
		con.update("UPDATE Ejecucion SET status = 'JUDGING' WHERE execID = "+ execID +" LIMIT 1 ;");

		//crear el nombre del archivo
		String fileName = 	"";
		if(LANG.equals("JAVA")){
			fileName = execID + ".java";			
		}

		if(LANG.equals("C")){
			fileName = execID + ".c";
		}

		if(LANG.equals("C++")){
			fileName = execID + ".cpp";
		}

		if(LANG.equals("Python")){
			fileName = execID + ".py";
		}

		if(LANG.equals("C#")){
			fileName = execID + ".cs";
		}

		if(LANG.equals("Perl")){
			fileName = execID + ".pl";
		}

		String rawFileName = 	"";

		//es un concurso ?
		boolean concurso = !concursoID.equals("-1");


		if(DEBUG) {
			System.out.println("execID     : " + execID);
			System.out.println("concursoID : " + concursoID);
			System.out.println("probID : " + probID);
			System.out.println("lenguage : " + LANG);
			System.out.println("userID : " + userID);
		}


		//agregar un nuevo intento a ese problema
		con.update("UPDATE Problema SET intentos = (intentos + 1) WHERE probID = "+ probID +" LIMIT 1 ");

		//agregar un nuevo intento a este chavo
		con.update("UPDATE Usuario SET tried = tried + 1  WHERE userID = '"+ userID +"' LIMIT 1 ;");

		//crear un directorio para trabajar con ese codigo
		File directorio = new File("../work_zone/" + execID);
		directorio.setWritable(true);
		directorio.mkdir();
		directorio.deleteOnExit();

		//crear un objeto File de el codigo fuente que se ha subido en la primer carpeta
		File cf = new File( "../codigos/" + fileName);
		cf.setWritable(true);


		//crer un objeto File donde se guardara el codigo fuente para ser compilado dentro de su sub-carpeta
		File cfNuevo = new File( directorio, fileName );
		try{
			cfNuevo.createNewFile();
		}catch(IOException ioe){
			System.out.println("Error al escribir en el disco duro.");
			return;
		}


		//copiar linea por linea el contenido en el archivo del work_zone
		try{
			BufferedReader br = new BufferedReader(new FileReader( cf ));
			PrintWriter pw = new PrintWriter( cfNuevo );

			String contents = "";
			while((contents = br.readLine()) != null){

				//aqui puedo ir revisando linea por linea por codigo malicioso
				pw.println( contents );
				
			}
			pw.flush();
			pw.close();

		}catch(IOException ioe){
			System.out.println("Error al transcribir el codigo fuente." + ioe);
			return;
		}


		//--------------compilar el codigo fuente-----------------------------------//
		// obvio depende de que voy a compilar

		//al constructor se le proporciona la ruta hasta el .java
		Compilador c = new Compilador();
		c.setLang( LANG );
		c.setFile( "../work_zone/" + execID +"/" + fileName );

		//verificar si compilo bien o no
		if( ! c.compilar() ){
			System.out.println("COMPILACION FALLIDA");

			//no compilo, actualizar la base de datos
			con.update("UPDATE Ejecucion SET status = 'COMPILACION' WHERE execID = "+ execID +" LIMIT 1 ;");

			//cerrar la conexion a la base
			terminarConexion();
	
			//salir
			return;
		}


		//brindarle los datos de entrada ahi en la carpeta
		//esos datos estan en la base de datos
		String titulo ;
		int tiempoLimite;

		rs = con.query("SELECT titulo, tiempoLimite FROM Problema WHERE probID = " + probID);
		try{
			rs.next();
			titulo  = rs.getString("titulo");
			tiempoLimite = Integer.parseInt ( rs.getString("tiempoLimite") );

		}catch(SQLException sqle){

			System.out.println("Error al contactar la BD.");
			return;
		}

		

		//generar el archivo de entrada para el programa
		File archivoEntrada = new File(directorio, "data.in");
		try{
			archivoEntrada.createNewFile();
		}catch(IOException ioe){
			System.out.println("Error al escribir el archivo de entrada." + ioe);
			return;
		}



		//llenar el contenido del archivo de entrada
		try{
			BufferedReader br = new BufferedReader( new FileReader( "../casos/"+probID+".in" ));
			PrintWriter pw = new PrintWriter( archivoEntrada );
			String s = null;
			while((s = br.readLine()) != null){
				pw.println( s );
			}	
			pw.flush();
			pw.close();

		}catch(IOException ioe){
			System.out.println("Error al transcribir el archivo de entrada." + ioe);
			return;
		}

		//eliminar el archivo de entrada al terminar el proceso
		//archivoEntrada.deleteOnExit();


		//--------------ejecutar lo que salga de la compilacion -----------------------------------//
		// 

		if(DEBUG) System.out.println("ejecutando...");

		//aqui esta lo bueno, ejecutar el codigo... sniff
		// por el momento al la clase ejecutar solo le pasaremos
		// el execID y con eso ejecutara el Main que este dentro o el a.out etc 
		Ejecutar e = new Ejecutar( execID );

		//decirle que lenguaje es... pudiera ser c, c++, python, etc
		e.setLang ( LANG );

		//la clase ejecutar es un hilo
		Thread ejecucion = new Thread(e);

		//comienza el tiempo
		long start = System.currentTimeMillis();

		//iniciar el hilo
		ejecucion.start();

		synchronized(ejecucion){
			try{
				//esperar hasta el tiempo limite
				ejecucion.wait( tiempoLimite );

			}catch(InterruptedException ie){
				//ni idea... :s
				System.out.println("thread interrumpido");
			}

			//al regresar, si el otro hilo sigue vivo entonces detenerlo
			if(ejecucion.isAlive()){
				//destruir el proceso... pero... como !
				//ejecucion.stop();
				e.destroyProc();
			}
		}


		//calcular tiempo total
		long tiempoTotal = System.currentTimeMillis() - start;

		//la varibale e.status contiene:
		//	TIEMPO 		si sobrepaso el limite de tiempo
		//	JUEZ_ERROR 	si surgio un error interno del juez
		//	EXCEPTION 	si el programa evaluado arrojo una exception

		if(DEBUG) System.out.println("resultado: "+ e.status);

		//revisar distintos casos despues de ejecutar el programa
		if( e.status.equals("TIME") ){
			//no cumplio en el tiempO
			System.out.println("TIEMPO");
			System.out.println("Tu programa fue detenido a los "+tiempoTotal+"ms");

			//guardar el resultado
			con.update("UPDATE Ejecucion SET status = 'TIEMPO', tiempo = "+ tiempoTotal +"  WHERE execID = "+ execID +" LIMIT 1 ;");

			//cerra base de datos
			terminarConexion();
			vaciarCarpeta( execID );

			//salir
			return;
		}


		if( e.status.equals("EXCEPTION") ){
			//arrojo una exception
			System.out.println("RUN-TIME ERROR");
			System.out.println("Tu programa ha arrojado una exception.");

			//guardar el resultado
			con.update("UPDATE Ejecucion SET status = 'RUNTIME_ERROR' WHERE execID = "+ execID +" LIMIT 1 ;");

			//cerra base de datos
			terminarConexion();
			vaciarCarpeta( execID );

			//salir
			return;
		}


		if( e.status.equals("JUEZ_ERROR") ){
			//arrojo una exception
			System.out.println("ERROR INTERNO EN EL JUEZ");

			//guardar el resultado
			con.update("UPDATE Ejecucion SET status = 'ERROR_JUEZ' WHERE execID = "+ execID +" LIMIT 1 ;");

			//cerra base de datos
			terminarConexion();
			vaciarCarpeta( execID );

			//salir
			return;
		}

		if(DEBUG) System.out.println("comprobando salida...");
		// ---------------------------------------------------------------------------- COMPROBAR SALIDA
		//si seguimos hasta aca, entonces ya solo resta compara el resultado
		//del programa con la variable salida
		String salidaTotal = "";


		int flag = 0;
		boolean erroneo = false;

		//leer los contenidos del archivo ke genero el programa he ir comparando linea por linea con la respuesta
		try{
			BufferedReader salidaDePrograma = new BufferedReader(new FileReader(new File(directorio, "data.out")));
			BufferedReader salidaCorrecta = new BufferedReader(new FileReader("../casos/" + probID + ".out"));

			String foo = null;
			String bar = null;
			while(((foo = salidaCorrecta.readLine()) != null) ){
				if((bar = salidaDePrograma.readLine()) == null) {
					erroneo = true;
					if(DEBUG) System.out.println("Se esperaban mas lineas de respuesta!!!") ;
					break;
				}


				if(DEBUG) System.out.println("ESPERADO : >" + foo + "<") ;
				if(DEBUG) System.out.println("RESPUESTA: >" + bar + "<") ;

				if(!foo.equals(bar)) {
					erroneo = true;
					if(DEBUG) System.out.println("^------ DIFF ------^") ;
				}
			}

			if((bar = salidaDePrograma.readLine()) != null) {
				if(! bar.trim().equals("")){
					erroneo = true;
					if(DEBUG) System.out.println("Ya acabde de leer la correcta pero tu programa tiene mas lineas") ;
					if(DEBUG) System.out.println("->"+bar) ;
				}
			}

		}catch(IOException ioe){
			System.out.println("NO SALIDA");


			con.update("UPDATE Ejecucion SET status = 'NO_SALIDA', tiempo = "+ tiempoTotal +"  WHERE execID = "+ execID +" LIMIT 1 ;");
			//cerra base de datos
			terminarConexion();
			vaciarCarpeta( execID );

			//salir
			return;						
		}

		if(DEBUG) System.out.println("erroneo : "+erroneo);

		if( !erroneo ){
			//programa correcto !
			System.out.println("OK");
			System.out.println("El tiempo fue de "+ tiempoTotal +"ms");

			//guardar el resultado de ejecucion
			con.update("UPDATE Ejecucion SET status = 'OK', tiempo = "+ tiempoTotal +"  WHERE execID = "+ execID +" LIMIT 1 ;");

			//darle un credito mas a este chavo solo si no ha resuelto este antes
			//revisar si ya lo ha resolvido
			rs = con.query("SELECT status FROM Ejecucion WHERE (probID = '" + probID +"' AND userID = '" + userID+"')" );
			int aciertos = 0;
			int intentos = 0;

			

			try{
				while(rs.next()){
					intentos++;
//					System.out.println("("+ intentos +")->"+rs.getString("status"));

					if( rs.getString("status").equals("OK") )
						aciertos++;

				}
			}catch(SQLException sqle){

				System.out.println("Error al contactar la BD.");
				return;
			}

//			System.out.println(intentos+ " "+ aciertos);
			//si no es asi, entonces sumarle uno

			if( aciertos == 1 ){
				con.update("UPDATE Usuario SET solved = solved + 1  WHERE userID = '"+ userID +"' LIMIT 1 ;");

			}else{
				System.out.println("Ya tenias resuelto este problema. Ya haz enviado "+ intentos +" soluciones para este problema. Y "+aciertos+" han sido correctas.");
			}


			//agregar un nuevo acierto al problema
			con.update("UPDATE Problema SET aceptados = (aceptados + 1) WHERE probID = "+ probID +" LIMIT 1 ");
		}else{
			//salida erronea
			System.out.println("WRONG");			
			System.out.println("El programa termino en "+tiempoTotal+"ms. Pero no produjo la respuesta correcta.");

			//guardar el resultado
			con.update("UPDATE Ejecucion SET status = 'INCORRECTO', tiempo = "+ tiempoTotal +"  WHERE execID = "+ execID +" LIMIT 1 ;");

		}


		//fin, terminar la conexion con la base de datos
		terminarConexion();
		vaciarCarpeta( execID );
	}


	static void vaciarCarpeta(String execID){

		//vaciar el contenido de la carpeta
		for( String file :  new File("../work_zone/"+execID).list() ){
			new File( "../work_zone/"+execID+"/"+file ).delete();
		}


	}


	//terminar la conexion con la base de datos
	private static void terminarConexion(){
		//cerrar conexion con base
		try{
			con.cerrar();
		}catch(Exception e){
			System.out.println("Error al cerrar la conexion con la base de datos.");
			return;
		}
	}
}




/* -------------------------------------------------------------------------------------------- *
* Conexion a la base de datos
*
* -------------------------------------------------------------------------------------------- */
class Conexion {

	static String bd ; 
	static String login;
	static String password;
	static String url; // = "jdbc:mysql://localhost/"+bd;

	Connection conexion = null;

	public Conexion() throws Exception {

		//leer el archivo config.php para sacar los datos de la base de datos
		System.out.println( "reading config.php for db conection..." );
		BufferedReader br = new BufferedReader(new FileReader("../www/config.php"));
		String s, foo;
		while((s = br.readLine()) != null){

			if(s.indexOf("$TEDDY_DB_SERVER") != -1){
				foo = s.substring( s.indexOf('\"') + 1, s.lastIndexOf('\"') );
				//System.out.println( "server:"+foo );
				url = "jdbc:mysql://"+foo+"/"+bd;
			}

			if(s.indexOf("$TEDDY_DB_USER") != -1){
				foo = s.substring( s.indexOf('\"') + 1, s.lastIndexOf('\"') );
				//System.out.println( "user:"+foo );
				login = foo;
			}

			if(s.indexOf("$TEDDY_DB_PASS") != -1){
				foo = s.substring( s.indexOf('\"') + 1, s.lastIndexOf('\"') );
				//System.out.println( "pass:"+foo );
				password = foo;
			}

			if(s.indexOf("$TEDDY_DB_NAME") != -1){
				foo = s.substring( s.indexOf('\"') + 1, s.lastIndexOf('\"') );
				//System.out.println( "name:"+foo );
				bd = foo;
			}


		}

	
		abrir();

   	}

	

	public void abrir() throws Exception {

		if (conexion == null){
			// /usr/lib/jvm/java-6-openjdk/jre/lib/ext
			Class.forName("org.gjt.mm.mysql.Driver");//cargamos el driver
	       	 	conexion = DriverManager.getConnection(url,login,password);//nos conectamos con la BD
	       	 	// System.out.println("Conexion activa");
		} else {
        		System.out.println("Existe una conexion activa a" + bd);
		}

	}


	public void cerrar() throws Exception{

		if(conexion != null){

			conexion.close();

			conexion = null;

			//System.out.println("Se cerro la conexion satisfactoriamente.");

		} else {

			System.out.println("No existe conexion que cerrar");
		}

	}

	

	public ResultSet query(String consulta) {
		try{
			Statement estado = conexion.createStatement();
			ResultSet rs = estado.executeQuery(consulta); 
			return rs;
		}catch(Exception e){
			System.out.println(e);
		}
		return null;
	}

	

	public int update(String consulta) {
		try{
			Statement estado = conexion.createStatement();
			int rs = estado.executeUpdate(consulta); 
			return rs;
		}catch(com.mysql.jdbc.exceptions.MySQLIntegrityConstraintViolationException micve){

		}catch(Exception e){
			System.out.println(e);
		}

		return -1;
	}
}

