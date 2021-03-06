<?php
	class LocalsController extends AppController {

		public $name = 'Locals';

		var $uses = array('Local','CategoriaLocal','Region','Comuna','User','Solicitud','Oferta','Producto','VotosLocal','Comentarios');

		public function beforeFilter() {
			$this->Auth->allow('locales','getLocal','getDatos');

			$this->current_user = $this->Auth->user();
			$this->logged_in = $this->Auth->loggedIn();
			$this->set('logged_in',$this->logged_in);
			$this->set('current_user',$this->current_user);
		}

		public function index() {
			if( $this->current_user['rol_id'] == 1) { 
				$this->set('locales', $this->Local->find('all',array(
					'order' => array('Local.nombre')
				)));
			}
			elseif( $this->current_user['rol_id'] == 3) {
				$conditions = array("Local.admin_id" => $this->current_user['id']);
				$locales = $this->Local->find('all', array('conditions' => $conditions, 'order' => array('Local.nombre')));
				$this->set('locales', $locales);
			}
			else	
				$this->redirect(array('action' => 'index'));
		}

		public function view($id) {
			$this->set('local', $this->Local->read(null,$id));
		}

		public function add() {
			$this->set('categorias',$this->CategoriaLocal->find('all',array(
				'order' => array('CategoriaLocal.nombre')
			)));
			$this->set('regiones',$this->Region->find('all',array(
				'order' => array('Region.nombre')
			)));
			$this->set('comunas',$this->Comuna->find('all',array(
				'order' => array('Comuna.nombre')
			)));

			if ($this->request->is('post')) {

				$this->set('nombre', $this->request->data['nombre']);
				$this->set('calle', $this->request->data['calle']);
				$this->set('numero', $this->request->data['numero']);
				$this->set('telefono_fijo', $this->request->data['telefono_fijo']);
				$this->set('telefono_movil', $this->request->data['telefono_movil']);
				$this->set('email', $this->request->data['email']);
				$this->set('sitio_web', $this->request->data['sitio_web']);

				$this->set('_categoria', $this->request->data['categoria_local_id']);
				$this->set('_comuna', $this->request->data['comuna_id']);
				$this->request->data['img']= "";

				$mensaje = '';

				if ($this->data['Image']) {
	                $image = $this->data['Image']['image'];
	                $imageTypes = array("image/gif", "image/jpeg", "image/png");
	                $uploadFolder = "img/upload/img_local";
	                $uploadPath = WWW_ROOT . $uploadFolder;
	               
	                foreach ($imageTypes as $type) {
	                	if($image['type'] == ""){
	                		$mensaje = "VACIO";
	                		$this->request->data['img']='/Hop/img/local.png';
	                	}
	                    elseif ($type == $image['type']) {
	                        if ($image['error'] == 0) {
	                            $imageName = $image['name'];
	                            
	                            if (file_exists($uploadPath . '/' . $imageName)) 
	                                $imageName = date('His') . $imageName;
	                            
	                            $full_image_path = $uploadPath . '/' . $imageName;
	                            
	                            if (move_uploaded_file($image['tmp_name'], $full_image_path)) {
	                                $mensaje ="EXITO";
	                                $this->set('imageName',$imageName);
	                                $ImagePath = '/Hop/img/upload/img_local/'.$imageName;
	            					$this->request->data['img']=$ImagePath;
	                            } 
	                            else
	                                $mensaje = 'Ha ocurrido un probema subiendo el archivo. Intente nuevamente.';
	                        } 
	                        else 
	                            $mensaje = 'Ha ocurrido un probema subiendo el archivo. Intente nuevamente.';
	                        break;
                    	} 	
                    	else 
                        	$mensaje = 'Tipo de archivo no soportado';
                	}
            	}

            	if($mensaje == "EXITO" || $mensaje == "VACIO"){
            		if(!$this->Local->findBynombre($this->request->data['nombre'])){
						if($this->current_user['rol_id'] == 1){
							$current_user = $this->Auth->user();
							$usuario = $this->User->findByusername($current_user['username']); 
							$this->request->data['user_id'] = $usuario['User']['id'];
							$this->request->data['region_id'] = 8;
							if ($this->Local->save($this->request->data)) {
								$this->Session->setFlash('El local ha sido guardado exitosamente.','default', array("class" => "alert alert-success"));
								$this->redirect(array('action' => 'index'));
							} 
							else 
								$this->Session->setFlash('El local no fue guardado, intente nuevamente.','default', array("class" => "alert alert-error"));
						} 

						elseif($this->current_user['rol_id'] != 1){
							$categorial = $this->CategoriaLocal->read(null,$this->request->data['categoria_local_id']);
							$comuna = $this->Comuna->read(null,$this->request->data['comuna_id']);
							$telefono_fijo = $this->request->data['telefono_fijo'];
							if($telefono_fijo == '') {
								$telefono_fijo = "null";
							}
							$telefono_movil = $this->request->data['telefono_movil'];
							if($telefono_movil == '') {
								$telefono_movil = "null";
							}

							$this->request->data['region_id'] = 8;
							$this->request->data['estado'] = "Pendiente";
							$this->request->data['sql'] = "INSERT INTO locals (\"nombre\",\"region_id\",\"comuna_id\",\"calle\",\"numero\",\"telefono_fijo\",\"telefono_movil\",\"email\",\"sitio_web\",\"categoria_local_id\",\"user_id\",\"created\",\"modified\",\"img\") VALUES ('".$this->request->data['nombre']."',".$this->request->data['region_id'].",".$this->request->data['comuna_id'].",'".$this->request->data['calle']."',".$this->request->data['numero'].",".$telefono_fijo.",".$telefono_movil.",'".$this->request->data['email']."','".$this->request->data['sitio_web']."',".$this->request->data['categoria_local_id'].",".$this->current_user['id'].",'".date("d-m-Y H:i:s")."','".date("d-m-Y H:i:s")."','".$this->request->data['img']."')";
							$this->request->data['accion'] = "Agregar";
							$this->request->data['tabla'] = "Locales";
							$this->request->data['campos'] = "Nombre: ".$this->request->data['nombre'].", CategoriaLocal: ".$categorial['CategoriaLocal']['nombre'].", Comuna: ".$comuna['Comuna']['nombre'].", Calle: ".$this->request->data['calle'].", Numero: ".$this->request->data['numero'].", TelefonoFijo: ".$telefono_fijo.", TelefonoMovil: ".$telefono_movil.", Email: ".$this->request->data['email'].", SitioWeb: ".$this->request->data['sitio_web'].", Usuario: ".$this->current_user['username'];
							$this->request->data['user_id'] = $this->current_user['id'];

							if ($this->Solicitud->save($this->request->data)) {
								$this->Session->setFlash('Su solicitud fue enviada exitosamente.','default', array("class" => "alert alert-success"));
								$this->redirect(array('controller' => 'Users' , 'action' => 'index'));
							} 
							else 
								$this->Session->setFlash('Su solicitud no fue enviada, intente nuevamente.','default', array("class" => "alert alert-error"));
						}
					}
					else
						$this->Session->setFlash('El nombre del local ya existe.','default', array("class" => "alert alert-error"));
            	}
            	else
            		$this->Session->setFlash($mensaje,'default', array("class" => "alert alert-error"));
			}
		}

		public function edit($id = null) {
			$this->set('local', $this->Local->read(null,$id));

			$this->set('categorias',$this->CategoriaLocal->find('all',array(
				'order' => array('CategoriaLocal.nombre')
			)));
				$this->set('regiones',$this->Region->find('all',array(
				'order' => array('Region.nombre')
			)));
				$this->set('comunas',$this->Comuna->find('all',array(
				'order' => array('Comuna.nombre')
			)));

			if ($this->request->is('post')) {

				$id = $this->request->data['id'];
				$nombre = $this->request->data['nombre'];

				$this->set('nombre', $nombre);

				$this->set('calle', $this->request->data['calle']);
				$this->set('numero', $this->request->data['numero']);
				$this->set('telefono_fijo', $this->request->data['telefono_fijo']);
				$this->set('telefono_movil', $this->request->data['telefono_movil']);
				$this->set('email', $this->request->data['email']);
				$this->set('sitio_web', $this->request->data['sitio_web']);

				$this->set('_categoria', $this->request->data['categoria_local_id']);
				$this->set('_comuna', $this->request->data['comuna_id']);
				$this->Local->set(array('modified' => date("d-m-Y H:i:s")));

				$conditions = array("Local.nombre" => $nombre,"Local.id !=" => $id);

				if ($this->data['Image']) {
	                $image = $this->data['Image']['image'];
	                $imageTypes = array("image/gif", "image/jpeg", "image/png");
	                $uploadFolder = "img/upload/img_local";
	                $uploadPath = WWW_ROOT . $uploadFolder;
	               
	                foreach ($imageTypes as $type) {
	                	if($image['type'] == ""){
	                		$mensaje = "VACIO";
	                	}
	                    elseif ($type == $image['type']) {
	                        if ($image['error'] == 0) {
	                            $imageName = $image['name'];
	                            
	                            if (file_exists($uploadPath . '/' . $imageName)) 
	                                $imageName = date('His') . $imageName;
	                            
	                            $full_image_path = $uploadPath . '/' . $imageName;
	                            
	                            if (move_uploaded_file($image['tmp_name'], $full_image_path)) {
	                                $mensaje ="EXITO";
	                                $this->set('imageName',$imageName);
	                                $ImagePath = '/Hop/img/upload/img_local/'.$imageName;
	            					$this->request->data['img']=$ImagePath;
	                            } 
	                            else
	                                $mensaje = 'Ha ocurrido un probema subiendo el archivo. Intente nuevamente.';
	                        } 
	                        else 
	                            $mensaje = 'Ha ocurrido un probema subiendo el archivo. Intente nuevamente.';
	                        break;
                    	} 	
                    	else 
                        	$mensaje = 'Tipo de archivo no soportado';
                	}
            	}

            	if($mensaje == "EXITO" || $mensaje == "VACIO"){
					if($this->Local->find('first', array('conditions' => $conditions))){
						$this->Session->setFlash('El nombre del local ya existe.','default', array("class" => "alert alert-error"));
					}
					else{
						if( ($this->current_user['rol_id'] == 1) || ($this->current_user['rol_id'] == 3 &&  $this->current_user['local_id'] == $id) ){
							if($this->Local->save($this->request->data)){
								$this->Session->setFlash('El local ha sido actualizado exitosamente.', 'default', array("class" => "alert alert-success"));
								$this->redirect(array('action' => 'index'));
							} 
							else{ 
								$this->Session->setFlash('El local no fue actualizado, intente nuevamente.','default', array("class" => "alert alert-error"));
							}
						}

						elseif( $this->current_user['rol_id'] == 2 || ($this->current_user['rol_id'] == 3 &&  $this->current_user['local_id'] != $id) ){
							$categorial = $this->CategoriaLocal->read(null,$this->request->data['categoria_local_id']);
							$comuna = $this->Comuna->read(null,$this->request->data['comuna_id']);
							$modified=date("d-m-Y H:i:s");

							$telefono_fijo = $this->request->data['telefono_fijo'];
							if($telefono_fijo == '') {
								$telefono_fijo = "null";
							}
							$telefono_movil = $this->request->data['telefono_movil'];
							if($telefono_movil == '') {
								$telefono_movil = "null";
							}

							$this->request->data['region_id'] = 8;
							$this->request->data['estado'] = "Pendiente";
							if($image['type'] != "" )
								$this->request->data['sql'] = "UPDATE locals Set nombre='".$this->request->data['nombre']."', region_id=".$this->request->data['region_id'].", comuna_id=".$this->request->data['comuna_id'].", calle='".$this->request->data['calle']."', numero=".$this->request->data['numero'].", telefono_fijo=".$telefono_fijo.", telefono_movil=".$telefono_movil.", email='".$this->request->data['email']."', sitio_web='".$this->request->data['sitio_web']."', categoria_local_id=".$this->request->data['categoria_local_id'].", user_id=".$this->current_user['id'].", modified='".$modified."', img='".$this->request->data['img']."' WHERE id=$id ";
							
							else
								$this->request->data['sql'] = "UPDATE locals Set nombre='".$this->request->data['nombre']."', region_id=".$this->request->data['region_id'].", comuna_id=".$this->request->data['comuna_id'].", calle='".$this->request->data['calle']."', numero=".$this->request->data['numero'].", telefono_fijo=".$telefono_fijo.", telefono_movil=".$telefono_movil.", email='".$this->request->data['email']."', sitio_web='".$this->request->data['sitio_web']."', categoria_local_id=".$this->request->data['categoria_local_id'].", user_id=".$this->current_user['id'].", modified='".$modified."' WHERE id=$id ";

							$this->request->data['local_id'] = $id;
							$this->request->data['accion'] = "Editar";
							$this->request->data['tabla'] = "Locales";
							$this->request->data['campos'] = "Nombre: ".$this->request->data['nombre'].", CategoriaLocal: ".$categorial['CategoriaLocal']['nombre'].", Comuna: ".$comuna['Comuna']['nombre'].", Calle: ".$this->request->data['calle'].", Numero: ".$this->request->data['numero'].", TelefonoFijo: ".$telefono_fijo.", TelefonoMovil: ".$telefono_movil.", Email: ".$this->request->data['email'].", SitioWeb: ".$this->request->data['sitio_web'].", Usuario: ".$this->current_user['username'];
							$this->request->data['user_id'] = $this->current_user['id'];

							if ($this->Solicitud->save($this->request->data)) {
								$this->Session->setFlash('Su solicitud fue enviada exitosamente.','default', array("class" => "alert alert-success"));
								$this->redirect(array('controller' => 'Users' , 'action' => 'index'));
							} 
							else 
								$this->Session->setFlash('Su solicitud no fue enviada, intente nuevamente.','default', array("class" => "alert alert-error"));
						}


					}
				}
				else
            		$this->Session->setFlash($mensaje,'default', array("class" => "alert alert-error"));
			} 
		}

		public function disable($id) {
			if ($this->request->is('post')) {
				throw new MethodNotAllowedException();
			} 
			else {
				$this->Local->read(null,$id);
				$this->Local->set(array('estado' => false));
				$this->Local->set(array('fecha_anulacion' => date("d-m-Y H:i:s")));

				if ($this->Local->save()) {
					$this->Session->setFlash('El local ha sido deshabilitado','default', array("class" => "alert alert-success"));
					$this->redirect(array('action' => 'index'));
				} 
				else {
					$this->Session->setFlash('El local no fue deshabilitado.','default', array("class" => "alert alert-error"));
	        		$this->redirect(array('action' => 'index'));
				}
			}
			
		}

		public function enable($id) {
			if ($this->request->is('post')) {
				throw new MethodNotAllowedException();
			} 
			else {
				$this->Local->read(null,$id);
				$this->Local->set(array('estado' => true));

				if ($this->Local->save()) {
					$this->Session->setFlash('El local ha sido habilitado','default', array("class" => "alert alert-success"));
					$this->redirect(array('action' => 'index'));
				} 
				else {
					$this->Session->setFlash('El local no fue habilitado.','default', array("class" => "alert alert-error"));
	        		$this->redirect(array('action' => 'index'));
				}
			}
		}

		public function informe() {
			$fecha_inicio = $this->request->data['fechaIni2'];
			$fecha_fin = $this->request->data['fechaFin2'];
			$tipoLocal = $this->request->data['tipoLocal'];

			$this->set('fecha_inicio', $fecha_inicio);
			$this->set('fecha_fin', $fecha_fin);
			$this->set('tipoLocal', $tipoLocal);

			$locales = array();

			if($tipoLocal == "Todos"){
				$locales = $this->Local->find('all',array(
		 						'order' => 'Local.created',
		 						'conditions' => array(
		 											'Local.created >=' => $fecha_inicio.' 00:00:00',
		 											'Local.created <=' => $fecha_fin.' 23:59:59',
		 										),
		 						'order' => array('Local.visitas' => 'desc')
		 					));

			} else if($tipoLocal == "Administrados"){
				$locales = $this->Local->find('all',array(
		 						'order' => 'Local.created',
		 						'conditions' => array(
		 											'Local.created >=' => $fecha_inicio.' 00:00:00',
		 											'Local.created <=' => $fecha_fin.' 23:59:59',
		 											"not" => array("Local.admin_id" => null)
		 										),
		 						'order' => array('Local.visitas' => 'desc')
		 					));
			} else {
				$locales = $this->Local->find('all',array(
		 						'order' => 'Local.created',
		 						'conditions' => array(
		 											'Local.created >=' => $fecha_inicio.' 00:00:00',
		 											'Local.created <=' => $fecha_fin.' 23:59:59',
		 											"Local.admin_id" => null
		 										),
		 						'order' => array('Local.visitas' => 'desc')
		 					));
			}
			
			$this->set('usuarios', $this->User->find('all'));
			$this->set('locales', $locales);
			$this->set('votos', $this->VotosLocal->find('all'));
    			
		}

		public function informe_anulados() {
			$fecha_inicio = $this->request->data['fechaIni5'];
			$fecha_fin = $this->request->data['fechaFin5'];
			$tipoLocal = $this->request->data['tipoLocalAnulado'];

			$this->set('fecha_inicio', $fecha_inicio);
			$this->set('fecha_fin', $fecha_fin);
			$this->set('tipoLocal', $tipoLocal);

			$locales = array();

			if($tipoLocal == "Todos"){
				$locales = $this->Local->find('all',array(
		 						'order' => 'Local.fecha_anulacion',
		 						'conditions' => array(
		 											'Local.fecha_anulacion >=' => $fecha_inicio.' 00:00:00',
		 											'Local.fecha_anulacion <=' => $fecha_fin.' 23:59:59',
		 											'Local.estado' => false
		 										)
		 					));

			} else if($tipoLocal == "Administrados"){
				$locales = $this->Local->find('all',array(
		 						'order' => 'Local.fecha_anulacion',
		 						'conditions' => array(
		 											'Local.fecha_anulacion >=' => $fecha_inicio.' 00:00:00',
		 											'Local.fecha_anulacion <=' => $fecha_fin.' 23:59:59',
		 											"not" => array("Local.admin_id" => null),
		 											'Local.estado' => false
		 										)
		 					));
			} else {
				$locales = $this->Local->find('all',array(
		 						'order' => 'Local.created',
		 						'conditions' => array(
		 											'Local.fecha_anulacion >=' => $fecha_inicio.' 00:00:00',
		 											'Local.fecha_anulacion <=' => $fecha_fin.' 23:59:59',
		 											"Local.admin_id" => null,
		 											'Local.estado' => false
		 										)
		 					));
			}
			
			$this->set('usuarios', $this->User->find('all'));
			$this->set('locales', $locales);
		}

		#========================Android==========================#

		public function locales(){
			$this->autoRender = false;

			$mensaje = '';
			$locales = array();

			if ($this->request->is('post')){

				$texto = $this->request->data['nombre'];
				
				$no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
				$permitidas= array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
				$texto = strtolower(str_replace($no_permitidas, $permitidas ,$texto));
				$producto = $this->Producto->findBynombre($texto);
				
				if(!empty($producto) and !is_null($producto)){
					$ofertas = $this->Oferta->find('all',array(
						 						'conditions' => array('Oferta.producto_id' => $producto['Producto']['id'])
						 					));

					if(!empty($ofertas) && !is_null($ofertas)){
						foreach ($ofertas as $index => $oferta){

							$local = $this->Local->find('first',array(
							 						'conditions' => array('Local.id' => $oferta['Oferta']['local_id'])
							 					));
							array_push($locales,$local['Local']);
						}

						$mensaje = "EXITO";
					}
					else
						$mensaje = 'El producto solicitado no esta disponible en los locales registrados.';
				}	
				else
					$mensaje = 'El producto solicitado no ha sido encontrado.';
			}

			$json['locales'] = $locales;
			$json['mensaje'] = $mensaje;
			echo json_encode($json);
		}

		public function getLocal(){
			$this->autoRender = false;

			$local = '';

			if ($this->request->is('post')){
				$local = $this->Local->find('first',array(
						 						'conditions' => array('Local.nombre' => $this->request->data['nombre'])
						 					));
			}

			echo json_encode($local['Local']);
		}

		public function getDatos(){
			$this->autoRender = false;

			$json = '';

			if ($this->request->is('post')){
				$comuna = $this->Comuna->read(null,$this->request->data['comuna']);
				$categoria_local = $this->CategoriaLocal->read(null,$this->request->data['categoria_local']);

				$votos_negativos = $this->VotosLocal->find('count', array('conditions' => array('VotosLocal.tipo' => 'negativo' , 'VotosLocal.local_id' => $this->request->data['id'])));
				$votos_positivos = $this->VotosLocal->find('count', array('conditions' => array('VotosLocal.tipo' => 'positivo' , 'VotosLocal.local_id' => $this->request->data['id'])));
			}

			$json['comunaNombre'] = $comuna['Comuna']['nombre'];
			$json['categoriaLocalNombre'] = $categoria_local['CategoriaLocal']['nombre'];
			$json['votosPositivos'] = $votos_positivos;
			$json['votosNegativos'] = $votos_negativos;
			echo json_encode($json);
		}
	}
?>