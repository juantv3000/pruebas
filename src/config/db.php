<?php
// Configuración acceso
class db{
        private $dbhost='localhost';
		private $dbuser='root';
		private $dbpass='70307030';
		private $dbname='base1';
		//Conexión PDO
		public function conexiondb(){
			                        $mysqlconect="mysql:host=$this->dbhost;dbname=$this->dbname;charset=utf8";									
									$dbconexion=new PDO($mysqlconect, $this->dbuser, $this->dbpass);
									$dbconexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
									return $dbconexion;
		                            }
}
?>