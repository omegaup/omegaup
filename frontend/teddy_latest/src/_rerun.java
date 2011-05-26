import java.io.*;
import java.sql.*;







class ReJudge {

	static private Conexion con    ;
	static private String userID   ;
	static private String probID   ;
	static private String execID   ;
	static private String LANG     ;
	static private String CARPETA  ;
	static private String FILENAME ;
	static private int TIEMPO_LIMITE = 20000;

	public static void main(String [] args){

		//crear conexion con base
		try{
			con = new Conexion();
		}catch(Exception e){
			System.out.println("Error al crear la conexion con la base de datos.");
			return;
		}

		execID = args[0];
			
		System.out.println(" <h2>Teddy Re-Judging</h2> ");


		//sacar info del run
		ResultSet rs = con.query( "SELECT * FROM Ejecucion WHERE execID = " + execID );

		//revisar si existe o no el execID
		try{

			if( !rs.next() ){
				System.out.println("Este run no existe !");
				return;
			}
			userID  = rs.getString("userID" );
			probID  = rs.getString("probID" );
			LANG    = rs.getString("LANG"   );

		}catch(SQLException sqle){

			System.out.println("Error al contactar la BD.\n" + sqle);
			return;
		}


		System.out.println( "<b>execID</b> " + execID );
		System.out.println( "<b>userID</b> " + userID );
		System.out.println( "<b>probID</b> " + probID );
		System.out.println( "<b>LANG</b> " + LANG );

		
		// en esta carpeta se ejecutara todo el pedo
		CARPETA = execID + "-rerun" + String.valueOf(Math.random()).substring(3,5) ;
		System.out.println( "<b>CARPETA DE RERUN</b> " + CARPETA );

		FILENAME = null;

		//detectar que lenguaje es
		if(LANG.equals("JAVA"))   FILENAME = execID + ".java";
		if(LANG.equals("C"))      FILENAME = execID + ".c";
		if(LANG.equals("C++"))    FILENAME = execID + ".cpp";
		if(LANG.equals("C#"))     FILENAME = execID + ".cs";
		if(LANG.equals("Python")) FILENAME = execID + ".py";
		if(LANG.equals("Perl"))   FILENAME = execID + ".pl";

		System.out.println( "<b>FILENAME</b> " + FILENAME );


		//revisar si existe el archivo de referencia
		File ref = new File( "../codigos/" + FILENAME);

		if(!ref.exists()){
			System.out.println("<b>EL ARCHIVO DE REFERENCIA NO EXISTE</b>");
			return;
		}

		//crear un directorio para trabajar con ese codigo
		File directorio = new File( "../work_zone/" + CARPETA );
		directorio.setWritable(true);
		directorio.mkdir();
		directorio.deleteOnExit();

		//escribir un nuevo archivo con el nombre Main
		String FILENAME_OUT = "";
		if(LANG.equals("JAVA"))   FILENAME_OUT = "Main.java";
		if(LANG.equals("C"))      FILENAME_OUT = "Main.c";
		if(LANG.equals("C++"))    FILENAME_OUT = "Main.cpp";
		if(LANG.equals("C#"))     FILENAME_OUT = "Main.cs";
		if(LANG.equals("Python")) FILENAME_OUT = "Main.py";
		if(LANG.equals("Perl"))   FILENAME_OUT = "Main.pl";
		
		//crer un objeto File donde se guardara el codigo fuente para ser compilado dentro de su sub-carpeta
		File cf = new File( directorio, FILENAME_OUT );
		try{
			cf.createNewFile();
		}catch(IOException ioe){
			System.out.println("Error al escribir en el disco duro: " + cf);
			System.out.println(ioe);			
			return;
		}


		//copiar linea por linea el contenido en esos dos archivos
		try{
			BufferedReader br = new BufferedReader(new FileReader( ref ));
			PrintWriter pw = new PrintWriter( cf );

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
		c.setFile( "../work_zone/" + CARPETA +"/" + FILENAME_OUT );

		//verificar si compilo bien o no
		if( ! c.compilar() ){
			System.out.println("<div align='center'>COMPILACION FALLIDA</div>");


			//cerrar la conexion a la base
			//terminarConexion();
	
			//salir
			return;
		}

		

		//generar el archivo de entrada para el programa
		File archivoEntrada = new File("../work_zone/"+ CARPETA, "data.in");
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

		
		System.out.println("ejecutando...");

		//aqui esta lo bueno, ejecutar el codigo... sniff
		// por el momento al la clase ejecutar solo le pasaremos
		// el execID y con eso ejecutara el Main que este dentro o el a.out etc 
		Ejecutar e = new Ejecutar( CARPETA );

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
				ejecucion.wait( TIEMPO_LIMITE );

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

		System.out.println("<div style='color:red'>resultado de la ejecucion: </div>"+ e.status);

		System.out.println("<b>duracion de la ejecucion</b> "+tiempoTotal+"ms");



		if( e.status.equals("EXCEPTION") ){
			//arrojo una exception
			System.out.println("<div style='color:red'>El programa ha arrojado una exception.</div>");

			//cerra base de datos
			//terminarConexion();
			//vaciarCarpeta( CARPETA );

			//salir
			return;
		}


		if( e.status.equals("JUEZ_ERROR") ){
			//arrojo una exception
			System.out.println("<b>ERROR INTERNO EN EL JUEZ</b>");


			terminarConexion();
			//vaciarCarpeta( CARPETA );


			//salir
			return;
		}

		System.out.println("comprobando salida...");
		// ---------------------------------------------------------------------------- COMPROBAR SALIDA
		//si seguimos hasta aca, entonces ya solo resta compara el resultado
		//del programa con la variable salida
		String salidaTotal = "";

		int flag = 0;
		boolean erroneo = false;

		//leer los contenidos del archivo ke genero el programa he ir comparando linea por linea con la respuesta
		try{
			BufferedReader salidaDePrograma = new BufferedReader(new FileReader(new File("../work_zone/"+CARPETA, "data.out")));
			BufferedReader salidaCorrecta = new BufferedReader(new FileReader("../casos/" + probID + ".out"));

			String foo = null;
			String bar = null;

			while(((foo = salidaCorrecta.readLine()) != null) ){
				if((bar = salidaDePrograma.readLine()) == null) {
					erroneo = true;
					System.out.println("Se esperaban mas lineas de respuesta!!!") ;
					break;
				}

				System.out.println("<pre>") ;
				System.out.println("ESPERADO : >" + foo + "< ") ;
				System.out.println("RESPUESTA: >" + bar + "<") ;
				System.out.println("</pre>") ;
				
				if(!foo.equals(bar)) {
					erroneo = true;
					System.out.println("<div style='color:red'>^------ DIFF ------^</div>") ;
				}else{
					System.out.println() ;
				}
			}

			if((bar = salidaDePrograma.readLine()) != null) {
				if(! bar.trim().equals("")){
					erroneo = true;
					System.out.println("Ya acabde de leer la correcta pero tu programa tiene mas lineas") ;
					System.out.println("->"+bar) ;
				}
			}

		}catch(IOException ioe){
			System.out.println("El juez no puede leer el archivo de salida, el juez solo busca el archivo 'data.out'.");

			terminarConexion();
			vaciarCarpeta( CARPETA );

			//salir
			return;						
		}

		if(erroneo)
			System.out.println("<div style='color:red'><b>Erroneo</b></div>");
		else
			System.out.println("<div style='color:GREEN'><b>Casos Correctos !</b></div>");
			



		//fin, terminar la conexion con la base de datos
		terminarConexion();
		vaciarCarpeta( CARPETA );
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

	}//main

}
