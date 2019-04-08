<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////
// INSTRUCCIONES                                                                                      //
////////////////////////////////////////////////////////////////////////////////////////////////////////
// EN FICHERO README.txt                                                                              //
////////////////////////////////////////////////////////////////////////////////////////////////////////

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app=new \Slim\App;
////////////////////////////////////////////////////////////////////////////////////////////////////////
// LISTADO DEL CALENDARIO CON FECHAS DISPONIBLES                                                      //
////////////////////////////////////////////////////////////////////////////////////////////////////////
$app->get('/api/calendario', function(Request $request, Response $response)
  {
  $sql="SELECT * FROM calendario";
   try{
	   $db=new db();
	   $db=  $db->conexiondb();
	   $resultado=$db->query($sql);
	   if($resultado->rowCount()>0)
	         {
		     $servicios=$resultado->fetchAll(PDO::FETCH_OBJ); 
             $servicios=json_encode($servicios);             
             $servicios=json_decode($servicios);
             $m=0;
             foreach($servicios as $servicio)
			       {
			       // COGEMOS DE CADA REGISTRO EL CAMPO HORARIO Y LO GUARDAMOS EN UN ARRAY
				   //////////////////////////////////////////////////////////////////////////
                   $horario=explode(",",$servicio->horario);
				   $fecha=$servicio->fecha;
				   $longitud=count($horario);
				   $disponibles="SELECT * FROM reservas";				   
				   $resultado_disponibles=$db->query($disponibles);
				   $disponibilidades=$resultado_disponibles->fetchAll(PDO::FETCH_OBJ);
				   // USAMOS DECODE Y ENCODE PARA PODER ACCEDER A LOS OBJETOS DE FORMA NORMAL
				   //////////////////////////////////////////////////////////////////////////
				   $disponibilidades=json_encode($disponibilidades);
				   $disponibilidades=json_decode($disponibilidades);
				   foreach($disponibilidades as $disponibilidad)
			             {
                         $hora=explode(",",$disponibilidad->hora);
                         $indice=$disponibilidad->indice;                         
				         // Extraemos los valores del array DIA con cualquier rango de dias que tenga
				         $fecha_reserva=$disponibilidad->dia;
				         //En función del tamaño del array buscamos coincidencias
                         for($x=0;$x<=$longitud-1;$x++)
                               {
				               // SI LA ENCONTRAMOS, ELIMINAMOS DEL ARRAY LA ENCONTRADA
                               if(isset($horario[$x]) && in_array($horario[$x],$hora) && $fecha_reserva==$fecha)
                                     {
				                     unset($horario[$x]);
				                     }  
				               }
				         // VOLVEMOS A GUARDAR LA NUEVA LISTA DE HORAS SIN LAS ENCONTRADAS
				         $servicios[$m]->horario=implode(",",$horario);
                         }
                         $m++;
                    }
             echo json_encode($servicios);                        
	         }
	         else
	            {
		        echo json_encode("No hay servicios");  
	            }
	   $resultado=null;
	   $resultado_disponibles=null;
	   $db=null;
       }catch(PDOException $e){
	                          echo '{"error" : {"text":'.$e.getMessage().'}';   
                              }
  });
////////////////////////////////////////////////////////////////////////////////////////////////////////
// LISTADO DE TODOS LOS SERVICIOS                                                                     //
////////////////////////////////////////////////////////////////////////////////////////////////////////

$app->get('/api/servicios', function(Request $request, Response $response)
  {
  $sql="SELECT * FROM servicios";
   try{
	   $db=new db();
	   $db=  $db->conexiondb();
	   $resultado=$db->query($sql);
	   if($resultado->rowCount()>0)
	         {
		     $servicios=$resultado->fetchAll(PDO::FETCH_OBJ); 
             echo $servicios=json_encode($servicios);
	         }
	         else
	            {
		        echo json_encode("No hay servicios");  
	            }
	   $resultado=null;
	   $db=null;
       }catch(PDOException $e)
            {
	        echo '{"error" : {"text":'.$e.getMessage().'}';   
            }
  });
////////////////////////////////////////////////////////////////////////////////////////////////////////
// AÑADIR SERVICIO                                                                                    //
////////////////////////////////////////////////////////////////////////////////////////////////////////
$app->post('/api/servicios/nuevo', function(Request $request, Response $response)
  {
  // DEFINIMOS VARIABLES
  /////////////////////////////////////////////////////////////////////
  $nombre=$request->getParam('nombre');
  $descripcion=$request->getParam('descripcion');
  $precio=$request->getParam('precio');
  $titulo_ingles=$request->getParam('titulo_ingles');
  $descripcion_ingles=$request->getParam('descripcion_ingles');
  $sql="INSERT INTO SERVICIOS(nombre,descripcion,precio,titulo_ingles,descripcion_ingles) VALUES (:nombre,:descripcion,:precio,:titulo_ingles,:descripcion_ingles)";
   try{
	   $db=new db();
	   $db= $db->conexiondb();
       $resultado_servicios=$db->prepare($sql);
	   $resultado_servicios->bindParam(':nombre',$nombre);
	   $resultado_servicios->bindParam(':descripcion',$descripcion);
	   $resultado_servicios->bindParam(':precio',$precio);
	   $resultado_servicios->bindParam(':titulo_ingles',$titulo_ingles);
	   $resultado_servicios->bindParam(':descripcion_ingles',$descripcion_ingles);
	   // EJECUTAMOS CONSULTA PARA AÑADIR NUEVA RESERVA	
	   /////////////////////////////////////////////////////////////////////    
	   $resultado_servicios->execute();
	   echo json_encode("Nuevo servicio creado correctamente.");
	   $resultado_servicios=null;
	   $db=null;
       }catch(PDOException $e)
            {
	        echo '{"error" : {"text":'.$e.getMessage().'}';   
            }
  });

////////////////////////////////////////////////////////////////////////////////////////////////////////
// LISTADO RESERVAS                                                                                   //
////////////////////////////////////////////////////////////////////////////////////////////////////////
$app->get('/api/servicios/listado-reservas', function(Request $request, Response $response)
  {
  $sql="SELECT * FROM reservas";
   try{
	   $db=new db();
	   $db=  $db->conexiondb();
	   $resultado=$db->query($sql);
	   if($resultado->rowCount()>0)
	         {
		     $reservas=$resultado->fetchAll(PDO::FETCH_OBJ);  
		     echo json_encode($reservas);
	         }
	         else
	            {
		        echo json_encode("No hay reservas");  
	            }
	   $resultado=null;
	   $db=null;
       }catch(PDOException $e)
            {
	        echo '{"error" : {"text":'.$e.getMessage().'}';   
            }
  });
  
////////////////////////////////////////////////////////////////////////////////////////////////////////
// AÑADIR NUEVA RESERVA                                                                               //
////////////////////////////////////////////////////////////////////////////////////////////////////////
$app->post('/api/servicios/reserva', function(Request $request, Response $response)
  {
  // DEFINIMOS VARIABLES
  /////////////////////////////////////////////////////////////////////
  $cliente=$request->getParam('cliente');
  $comentarios=$request->getParam('comentarios');
  $dia=$request->getParam('dia');
  $hora=$request->getParam('hora');
  $longitud_horas=0;
  $service=$request->getParam('servicio');
  $precio_total=$request->getParam('precio_total');	
  // CONSTRUIMOS CONSULTA DE INSERCIÓN Y LA DEJAMOS PREPARADA
  ////////////////////////////////////////////////////////////////////
  $sql="INSERT INTO RESERVAS(cliente,comentarios,dia,hora,servicio,precio_total) VALUES (:cliente,:comentarios,:dia,:hora,:servicio,:precio_total)";
  try{
     $db=new db();
     $db=$db->conexiondb();
     // COMPROBAMOS HORAS DE LAS RESERVAS HECHAS 	
     /////////////////////////////////////////////////////////////////////			   
     $horarios="SELECT * FROM reservas";  
     $resultado_horarios=$db->query($horarios);
     if($resultado_horarios->rowCount()>0)
	       {
		   $horarios=$resultado_horarios->fetchAll(PDO::FETCH_OBJ); 
           $horarios=json_encode($horarios);
		   $horarios=json_decode($horarios);
		   $en_horario=0;
		   foreach($horarios as $horario)
		   	     {
			 	 $abierto=explode(",",$horario->hora);
				 $fecha=$horario->dia;
				 $longitud_horas=count($abierto);
				 $hora_actual=explode(",", $hora);			
				 for($x=0;$x<=$longitud_horas-1;$x++)
				       {
					   if(in_array($abierto[$x],$hora_actual) && $dia==$fecha)
					         {
							 $en_horario+=1;
							 }
					   }
                 }
	       }
	       else
		    {
		    echo json_encode("No hay RESERVAS");  
	        }                         							   
       // MIRAMOS SI HA HABIDO ALGUNA COINCIDENCIA	
	   /////////////////////////////////////////////////////////////////////
       if($en_horario>0)
	         {
		     echo json_encode("Una o varias horas no estan disponibles para ese día.");   
	         }
         else
		    {
		    $precio_total=$longitud_horas*$precio_total;
            $resultado_reserva=$db->prepare($sql);
	        $resultado_reserva->bindParam(':cliente',$cliente);
	        $resultado_reserva->bindParam(':comentarios',$comentarios);
	        $resultado_reserva->bindParam(':dia',$dia);
	        $resultado_reserva->bindParam(':hora',$hora);
	        $resultado_reserva->bindParam(':servicio',$service);
	        $resultado_reserva->bindParam(':precio_total',$precio_total);
	   // EJECUTAMOS CONSULTA PARA AÑADIR NUEVA RESERVA SIN DUPLICADOS	
	   /////////////////////////////////////////////////////////////////////    
	        $resultado_reserva->execute();
	        echo json_encode("Nueva reserva realizada");
            }	   
	   $resultado_reserva=null;
	   $resultado_horarios=null;
	   $db=null;
       }catch(PDOException $e)
	         {
	         echo '{"error" : {"text":'.$e.getMessage().'}';   
             }
   });

?>