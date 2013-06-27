<?php

	class UsersController extends AppController {
		
		public $name = 'Users';

		var $uses = array('User','Region','Comuna');

		var $sacaffold;

		public function beforeFilter(){
			parent::beforeFilter();
			$this->Auth->allow('add');
		}
		
		public function index() {
			$this->set('usuarios', $this->User->find('all'));
		}

		public function view($id) {
			$this->User->id = $id;
			$this->set('usuario', $this->User->read());
		}

		public function add() {
			$this->set('regiones',$this->Region->find('all'));
			$this->set('comunas',$this->Comuna->find('all'));

			if ($this->request->is('post')) {
				$rut = $this->request->data['rut'];
				$username = $this->request->data['username'];
				$email = $this->request->data['email'];
				
				if($this->User->findByrut($rut)){
					$this->Session->setFlash('El rut '.$rut.' ya esta registrado.','default', array("class" => "alert alert-error"));
					$this->redirect(array('action' => 'add'));
				} elseif($this->User->findByusername($username)){
					$this->Session->setFlash('El username '.$username.' ya esta registrado.','default', array("class" => "alert alert-error"));
					$this->redirect(array('action' => 'add'));
				} elseif($this->User->findByemail($email)){
					$this->Session->setFlash('El mail '.$email.' ya esta registrado.','default', array("class" => "alert alert-error"));
					$this->redirect(array('action' => 'add'));
				} else{
					$this->request->data['rol_id'] = 1;

					if ($this->User->save($this->request->data)) {
						$this->Session->setFlash('El usuario ha sido guardado exitosamente.','default', array("class" => "alert alert-success"));
						$this->redirect(array('action' => 'add'));
					} else {
						$this->Session->setFlash('El usuario no fue guardado, intente nuevamente.','default', array("class" => "alert alert-error"));
						$this->redirect(array('action' => 'add'));
					}
				} 
			}
		}

		function edit($id = null) {
			$this->User->id = $id;
			if ($this->request->is('get')) {
				$this->request->data = $this->User->read();
			} elseif ($this->User->save($this->request->data)) {
					$this->Session->setFlash('El usuario ha sido actualizado exitosamente.','default', array("class" => "alert alert-success"));
					$this->redirect(array('action' => 'edit'));
			} else {
				$this->Session->setFlash('El usuario no fue actualizado, intente nuevamente.','default', array("class" => "alert alert-error"));
				$this->redirect(array('action' => 'edit'));
			}
			
		}

		public function delete($id) {
			if (!$this->request->is('post')) {
				throw new MethodNotAllowedException();
			}
			if ($this->User->delete($id)) {
				$this->Session->setFlash('El usuario ha sido eliminado.','default', array("class" => "alert alert-success"));
				$this->redirect(array('action' => 'delete'));
			}
			$this->Session->setFlash('El usuario no fue eliminado.','default', array("class" => "alert alert-error"));
        	$this->redirect(array('action' => 'delete'));
		}

		public function login(){
			if($this->request->is('post')){
				if($this->Auth->login()){
					$this->redirect($this->Auth->redirect());
				}
				else{
					$this->Session->setFlash('Error iniciando sesión, username o password incorrecto','default', array("class" => "alert alert-error"));
        			$this->redirect(array('action' => 'login'));
				}
			}
		}

		public function logout(){
			$this->redirect($this->Auth->logout());
		}
	}
?>