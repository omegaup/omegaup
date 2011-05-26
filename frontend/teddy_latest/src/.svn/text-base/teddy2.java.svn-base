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
			 comando = "./compileC " + test[1] + " " + test[2]; //comando = "gcc " + fileName;
		}

		if(LANG.equals("C++")){
			 String [] test = fileName.split("/");
			 comando = "./compileC++ " + test[1] + " " + test[2]; 
		}

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
				boolean impMsg = true;
				String linea2 = null;
				while((linea2 = br2.readLine()) != null){
					if(impMsg){
						System.out.println("<br><b>El compilador dice:</b>");
						impMsg = false;
					}
					System.out.println( linea2 );
				}

			}

		}catch(Exception e){
			//error interno del juez
			System.out.println("<div align='center'><b>ERROR EN EL JUEZ</b>, no se ha podido compilar, porfavor reporta este error</div><br>" + e);
			return false;
		}
		

		//si pudo compilar el juez
		//depende lo que regrese el compilador es si si compilo o no compilo
		return (exitVal == 0);
	}

	//constructor
	Compilador( ){
	}

	void setLang( String LANG ){
		this.LANG = LANG;
	}

	void setFile( String fileName ){
		this.fileName = fileName;
	}

}





class Ejecutar implements Runnable{

	public String status = "TIME";	

	private StringBuilder salidaFinal = null;
	private String execID;
	private String LANG;
	private Process proc;
	private String PID;
	private String comando;
	private String killcomand;
	private String PROBID;


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

	StringBuilder getSalida(){
		return salidaFinal;
	}

	//set language to execute
	void setLang( String lang ){
		LANG = lang;
	}

	void setProb( String probID ){
		PROBID = probID;
	}

	//constructor
	Ejecutar(String s){
		this.execID = s;
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
			StringBuilder salida = new StringBuilder();
			String salida_err = "";
			PID = comando;

			try{
				//ejecutar el script
				proc = Runtime.getRuntime().exec(comando);
				
				System.out.println( "Total meoria de la maquina virutal : " +  (Runtime.getRuntime().totalMemory() / 1024) + "kb");
				
				//suministrarle datos por la entrada estandar
				BufferedOutputStream bos = new BufferedOutputStream( proc.getOutputStream() );
				
					//leer de el archivo data.in
					BufferedReader cin = new BufferedReader( new FileReader( "casos/"+PROBID+".in" ) );
					int caracter;
					while( (caracter = cin.read() ) != -1 ){
						bos.write(caracter);
					}
					
					bos.flush();			
					bos.close();				

				
				//leer salida estandar
				InputStreamReader isr = new InputStreamReader( proc.getInputStream() );
				BufferedReader br = new BufferedReader(isr);
				
				String linea = null;
				while((linea = br.readLine()) != null)
					salida.append(linea + "\n");


				//leer salida de error
				InputStreamReader isr2 = new InputStreamReader( proc.getErrorStream() );
				BufferedReader br2 = new BufferedReader( isr2 );
				
				String linea2 = null;
				while((linea2 = br2.readLine()) != null)
					salida_err += linea2 + "\n";

				

				//esperar a que termine el proceso
				exitVal = proc.waitFor();
				
			} catch( Exception e ) {

				//error interno del juez
				//status = "ERROR_JUEZ";
				System.out.println("TEDDY: Error, el juez no ha podido ejecutar el programa. \n" + e);
				//return;
			}

			//alguna exception del progrma invitado
			if( exitVal != 0 ) { 
				//System.out.println(exitVal);
				status = "EXCEPTION";
				return; 
			}
			
/*			System.out.println("---- salida --- ");
			System.out.println(salida);
			System.out.println("---- salida --- ");
*/

			//poner la salida final
			salidaFinal = salida;
			
			//poner ok, en la ejecucion
			status = "OK";
			
			//avisar al otro hilo que hemos terminado
			notify();
		}
	}//run code thread



}//clase ejecutar



/* -------------------------------------------------------------------------------------------- *
* Juez - Revisa la base de datos por codigos que no esten jueceados
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
			System.out.println(e); 
			return;
		}

		boolean debug = false;
		for(String foobar : args)
			if( foobar.equals("DEBUG") )
				debug = true;
			
		DEBUG = debug;

		if(DEBUG) System.out.println("MODO DEBUG ACTIVADO");

		//variables que vienen de php
		String execID   = 	args[0].split("_")[0];
		String fileName = 	args[0].split("_")[1];
		String rawFileName = 	args[0];
		String concursoID =	"-1"   ;
		boolean concurso = false;
		String userID = null;
		String probID = null;


		if(DEBUG) {
			System.out.println("execID     : " + execID);
			System.out.println("fileName   : " + fileName);
			System.out.println("concursoID : " + concursoID);
			System.out.println("rawFileName   : " + rawFileName);
		}


		//detectar que lenguaje es
		LANG = "NONE";
		if( fileName.endsWith(".java") ) 	LANG = "JAVA";
		if( fileName.endsWith(".c") ) 		LANG = "C";
		if( fileName.endsWith(".cpp") ) 	LANG = "C++";
		if( fileName.endsWith(".cs") ) 		LANG = "C#";
		if( fileName.endsWith(".py") ) 		LANG = "Python";
		if( fileName.endsWith(".pl") ) 		LANG = "Perl";



		//mostrar en pantalla			
		System.out.println("ID de ejecucion : <b>"+ execID +"</b>");

		ResultSet rs = con.query( "SELECT * FROM Ejecucion WHERE execID = " + execID );

		try{
			rs.next();
			userID = rs.getString("userID");
			probID  = rs.getString("probID");

		}catch(SQLException sqle){

			System.out.println("TEDDY: Error al contactar la BD.");
			return;
		}



		if(DEBUG) {
			System.out.println("userID     : " + userID);
			System.out.println("probID     : " + probID);
		}

		//agregar un nuevo intento a ese problema
		con.update("UPDATE Ejecucion SET LANG = '"+LANG+"' WHERE execID = "+ execID +" LIMIT 1 ");

		//agregar un nuevo intento a ese problema
		con.update("UPDATE Problema SET intentos = (intentos + 1) WHERE probID = "+ probID +" LIMIT 1 ");

		//agregar un nuevo intento a este chavo si no es concurso
		con.update("UPDATE Usuario SET tried = tried + 1  WHERE userID = '"+ userID +"' LIMIT 1 ;");


		//crear un directorio para trabajar con ese codigo
		File directorio = new File("work_zone/" + execID);
		//directorio.setWritable(true);
		directorio.mkdir();
		directorio.deleteOnExit();

		//crear un objeto File de el codigo fuente que se ha subido en la primer carpeta
		File cf = new File( "work_zone/" + rawFileName);
		//cf.setWritable(true);
		//cf.deleteOnExit();


		//crer un objeto File donde se guardara el codigo fuente para ser compilado dentro de su sub-carpeta
		File cfNuevo = new File( directorio, fileName );
		try{
			cfNuevo.createNewFile();
		}catch(IOException ioe){
			System.out.println("TEDDY: Error al escribir en el disco duro.");
			return;
		}


		//crer un objeto File donde se guardara el codigo para futura referencia, agregando el execID y la extension de filename
		File referencia = new File( "codigos/" + execID + fileName.substring(fileName.lastIndexOf("."), fileName.length()) );
		try{
			referencia.createNewFile();
		}catch(IOException ioe){
			System.out.println("TEDDY: Error al escribir en el disco duro el archivo de referencia.");
			return;
		}

		//copiar linea por linea el contenido en esos dos archivos
		try{
			BufferedReader br = new BufferedReader(new FileReader( cf ));
			PrintWriter pw = new PrintWriter( cfNuevo );
			PrintWriter pw2 = new PrintWriter( referencia );

			String contents = "";
			while((contents = br.readLine()) != null){

				//aqui puedo ir revisando linea por linea por codigo malicioso
				pw.println( contents );
				pw2.println( contents );
				
			}
			pw2.flush();
			pw2.close();
			pw.flush();
			pw.close();

		}catch(IOException ioe){
			System.out.println("TEDDY: Error al transcribir el codigo fuente: " + ioe);
			return;
		}


		//--------------compilar el codigo fuente-----------------------------------//
		// obvio depende de que voy a compilar

		//al constructor se le proporciona la ruta hasta el .java
		Compilador c = new Compilador();
		c.setLang( LANG );
		c.setFile( "work_zone/" + execID +"/" + fileName.substring(fileName.lastIndexOf("_")+1, fileName.length()) );

		//verificar si compilo bien o no
		if( ! c.compilar() ){
			System.out.println("<div align='center'><h2>COMPILACION FALLIDA</h2></div>");
			System.out.println("<div align='center'>Tu programa no ha compilado correctamente.</div>");

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

			System.out.println("TEDDY: Error al contactar la BD.");
			return;
		}


		//mostrar titulo del problema
		System.out.println("Problema: <b>" + probID + ".</b> "+ titulo);
	


		//--------------ejecutar lo que salga de la compilacion -----------------------------------//
		// 

		if(DEBUG) System.out.println("ejecutando...");

		//aqui esta lo bueno, ejecutar el codigo... sniff
		// por el momento al la clase ejecutar solo le pasaremos
		// el execID y con eso ejecutara el Main que este dentro o el a.out etc 
		Ejecutar e = new Ejecutar( execID );

		//decirle que lenguaje es... pudiera ser c, c++, python, etc
		e.setLang ( LANG );

		//decirle a 'Ejecutar' de que caso debe sacar la entrada
		e.setProb( probID ); 

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
			System.out.println("<div align='center'><h2>TIEMPO</h2></div>");
			System.out.println("<div align='center'>Tu programa fue detenido a los "+tiempoTotal+"ms</div>");

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
			System.out.println("<div align='center'><h2>RUN-TIME ERROR</h2></div>");
			System.out.println("<div align='center'>Tu programa ha arrojado una exception.</div>");

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
			System.out.println("<b>ERROR INTERNO EN EL JUEZ</b>");

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
		StringBuilder salidaTotal = e.getSalida();
		salidaTotal.trimToSize();


		int flag = 0;

		//leer los contenidos del archivo ke genero el programa he ir comparando linea por linea con la respuesta

		StringBuilder salidaCorrectaSB = null;
		
		try{
			BufferedReader salidaCorrecta = new BufferedReader(new FileReader("casos/" + probID + ".out"));
			salidaCorrectaSB = new StringBuilder();
			
			String foo;
			while(((foo = salidaCorrecta.readLine()) != null) ){
					salidaCorrectaSB.append( foo + "\n" );
			}

		}catch(IOException ioe){
			System.out.println("TEDDY: error al leer la salida del caso de prueba " + ioe);

			//cerra base de datos
			terminarConexion();
			vaciarCarpeta( execID );

			//salir
			return;						
		}

		if( salidaTotal.toString().length() == 0 ){
				System.out.println("<div align='center'>Tu Programa no escribio nada a la salida estandar <b> : ( </b></div>");
				//con.update("UPDATE Ejecucion SET status = 'NO_SALIDA', tiempo = "+ tiempoTotal +"  WHERE execID = "+ execID +" LIMIT 1 ;");
		}

//		System.out.println( salidaCorrectaSB );
//		System.out.println( salidaTotal );

		boolean erroneo = !salidaCorrectaSB.toString().trim().equals( salidaTotal.toString().trim() );
		
		if( !erroneo ){
			//programa correcto !
			System.out.println("<div align='center'><h2>OK</h2></div>");
			System.out.println("<div align='center'><b>Acepta estas felicitaciones automaticas !</b><br>Tu tiempo fue de "+ tiempoTotal +"ms</div>");

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

			String [] congrats = { "Enviaremos tu curriculum a Google !", "Revisaremos si estas haciendo trampa !", "Teddy esta conmovido :P"
			 , "Seguro que eres humano ? :-P", "Dicen que si haces click en los anuncios de Teddy te haces mejor programdor ! :P", 
			 "Deberias ir a uno de los concursos del Tecnologico de Celaya :D"};
			
			int congrat_num  = (int)(Math.random() * congrats.length);

			if( aciertos == 1 ){
				con.update("UPDATE Usuario SET solved = solved + 1  WHERE userID = '"+ userID +"' LIMIT 1 ;");

				if(intentos == 1)
					System.out.println("<div align='center'><b>WOW</b> Haz resuelto este problema en tu primer intento. "+congrats[congrat_num]+"</div>");
				else
					System.out.println("<div align='center'>Te ha tomado "+intentos+" intentos resolver este problema. Dice Teddy que ni estaba tan dificil :P</div>");

			}else{
				System.out.println("<div align='center'><b>Ya tenias resuelto este problema.</b> Ya haz enviado "+ intentos +" soluciones para este problema. Y "+aciertos+" han sido correctas. </div>");
			}


			//agregar un nuevo acierto al problema
			con.update("UPDATE Problema SET aceptados = (aceptados + 1) WHERE probID = "+ probID +" LIMIT 1 ");
		}else{
			
			String [] no_congrats = { "Vamos ! Un intento mas !", "Sigue intentando, teddy confia en ti :D", "No te des por vencido !"};
			
			int no_congrat_num  = (int)(Math.random() * no_congrats.length);
			
			//salida erronea
			System.out.println("<div align='center'><h2>WRONG</h2></div>");			
			System.out.println("<div align='center'>Tu programa termino en "+tiempoTotal+"ms. Pero no produjo la respuesta correcta. "+no_congrats[no_congrat_num]+"</div>");

			//guardar el resultado
			con.update("UPDATE Ejecucion SET status = 'INCORRECTO', tiempo = "+ tiempoTotal +"  WHERE execID = "+ execID +" LIMIT 1 ;");

		}


		//fin, terminar la conexion con la base de datos
		terminarConexion();
		vaciarCarpeta( execID );
	}


	static void vaciarCarpeta(String execID){

		//vaciar el contenido de la carpeta
		for( String file :  new File("work_zone/"+execID).list() ){
			new File( "work_zone/"+execID+"/"+file ).delete();
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

	static String bd = "teddy";
	static String login = "root";
	static String password = "";
	static String url = "jdbc:mysql://localhost/"+bd;

	Connection conexion = null;

	public Conexion() throws Exception {
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

