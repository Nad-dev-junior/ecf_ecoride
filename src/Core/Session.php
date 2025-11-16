<?php

namespace Ecoride\Ecoride\Core;

class Session {
    public function __construct(){
        if(session_status()=== PHP_SESSION_NONE){
            session_start();
        }
    }

    public function set_session(string $key, mixed $value):void{
        $_SESSION[$key]=$value;
    }
    public function get_session(string $key, $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function remove_session(string $key):void{
        unset($_SESSION[$key]);
    }

    public function destroy_session():void{
        session_destroy();
    }

    public function has_session(string $key):bool{
        return isset($_SESSION[$key]);
    }

    public function set_flash(string $type, string $message):void{
        $_SESSION['flash'][$type]=$message;
    }

    public function get_flash($type)
    {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }

    public function has_flash(string $type): bool
    {
        return isset( $_SESSION['flash'][$type]);
    }

}