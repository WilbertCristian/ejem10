<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController{
    //paso 1 --> iniciar y cerrar sesion
    public static function login(Router $router){
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $auth = new Usuario($_POST);
            $alertas = $auth->validarLogin();
            if(empty($alertas)){
                //comporbar si el usuario existe
                $usuario = Usuario::where('email', $auth->email);
                if($usuario){
                    //verificar el password
                    if($usuario->comprobarPasswordAndVerificado($auth->password)){
                        //autenticar al usuario
                        session_start();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre . " " . $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['telefono'] = $usuario->telefono;
                        $_SESSION['login'] = true;

                        //redireccionamiento
                        if($usuario->admin === "1"){
                            $_SESSION['admin'] = $usuario->admin ?? null;
                            header("Location: /admin");
                        }else{
                            // debuguear('Es cliente');
                            header('Location: /cita');
                        }
                        // debuguear($_SESSION);
                    }
                }else{
                    Usuario::setAlerta('error', 'Usuario no encontrado');
                }
            }
        }
        $alertas = Usuario::getAlertas();
        $router->render('auth/login', [
            'alertas' => $alertas
        ]);
    }   
    public static function logout(){
        session_start();
        $_SESSION = [];
        header('Location:/');
    } 

    //paso 2 --> en saco de olvidar y recuperar
    public static function olvide(Router $router){
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $auth = new Usuario($_POST);
            $alertas = $auth->validarEmail();

            if(empty($alertas)){
                $usuario = Usuario::where('email', $auth->email);

                if($usuario && $usuario->confirmado === "1"){
                    //generar un token
                    $usuario->crearToken();
                    $usuario->guardar();

                    //enviar el email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();

                    //alerta éxito
                    Usuario::setAlerta('exito', 'revisa tu email');
                    // $alertas = Usuario::getAlertas();
                }else{
                    Usuario::setAlerta('error', 'El usuario no existe'); 
                }
            }
        }
        $alertas = Usuario::getAlertas();
        $router->render('auth/olvide-password',[
            'alertas' => $alertas
        ]);
    } 
    public static function recuperar(Router $router){
        $alertas = [];
        $error = false;
        $token = s($_GET['token']);

        //buscar usuario por su token para
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)){
            Usuario::setAlerta('error', 'Token no valido');
            $error = true;
        }
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            // leer el nuevo password y guargarlo

            $password = new Usuario($_POST);
            $alertas = $password->validarPassword();
            if(empty($alertas)){
                $usuario->password = null;

                $usuario->password = $password->password;
                $usuario->hashPassword();
                $usuario->token = null;

                $resultado = $usuario->guardar();
                if($resultado){
                    header('Location: /');
                }
            }  
        }

        // debuguear($usuario);

        $alertas = Usuario::getAlertas();
        $router->render('auth/recuperar-password', [
            'alertas' => $alertas,
            'error' => $error
        ]);
    }
    
    //paso 3 crear cuenta
    public static function crear(Router $router){
      
        $usuario = new Usuario;
       //alertas vacias
       $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            // echo 'Enviaste el formulario';
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();
            
            //revisa que alerta este vacio
            if(empty($alertas)){
                //verificar que el usuario no este registrado
                $resultado = $usuario->existeUsuario();
                if($resultado->num_rows){
                    $alertas = Usuario::getAlertas();
                }else{
                    //hashar el password
                    $usuario->hashPassword();
                    //generar token
                    $usuario-> crearToken();
                    //enviar el formulario enviar email de confirmacion
                    $email = new Email($usuario->nombre, $usuario->email, $usuario->token);
                    $email->enviarConfirmacion();

                    //crar el usuario
                    $resultado = $usuario->guardar();
                    // debuguear($usuario);
                    if($resultado){
                        header('Location: /mensaje');
                    }
                }
            }
        }
        $router->render('auth/crear-cuenta', [
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }
    public static function mensaje(Router $router){
        $router->render('auth/mensaje');
    } 
    public static function confirmar(Router $router){
        $alertas = [];
        //sanitizar token
        $token = s($_GET['token']);
        $usuario = Usuario::where('token', $token);
        if(empty($usuario)){
            //mostrar mensaje de erro
            Usuario::setAlerta('error', 'Token no valido');
        }else{
            //modificar al usuario confirmado
            $usuario->confirmado = "1";
            $usuario-> token = null;
            $usuario->guardar();
            Usuario::setAlerta('exito', 'Cuenta comprobada correctamente.');
            // debuguear($usuario);    
        }
        $alertas = Usuario::getAlertas();
        //renderisa vista
        $router->render('auth/confirmar-cuenta', [
            'alertas'=>$alertas
        ]);
    }
}