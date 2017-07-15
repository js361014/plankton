<?php

namespace Rest\Server;


use Rest\Server\Request;
use Rest\Server\Response;
use Rest\Exception;
use Rest\NotFoundException;

class Server{
	/**
	 * @access protected
	 * @var Controller[]
	 */
	protected $controllers;
	
	/**
	 * @access protected
	 * @var \Rest\Server\Request
	 */
	protected $request;
	
	/**
	 * @access public
	 * @return void
	 */
	public function __construct(){
		$this->controllers = [];
		$this->request = new Request();
	}
	
	/**
	 * @access public
	 * @return Server
	 */
	public function registerController(Controller $controller){
		$this->controllers[$controller->getName()] = $controller;
		
		return $this;
	}

	/**
	 * @access public
	 * @throws \RuntimeException
	 * @return void
	 */
	public function run(){		
		try{
			$this->handleRequest();
		}
		catch (Exception $e) {
			$this->handleException($e);
		}
	}
	
	/**
	 * @access protected
	 * @param \Rest\Server\Response $response
	 * @throws \RuntimeException
	 * @return void
	 */
	protected function send(Response $response){
		if (headers_sent()) {
			throw new \RuntimeException("headers already sent");
		}
		
		http_response_code($response->getCode());

		foreach ($response->getHeaders() as $name => $value) {
			header($name . ": " . $value);	
		}
		
		echo $response;
	}
	
	/**
	 * @access protected
	 * @throws \Rest\Exception
	 * @return void
	 */
	protected function handleRequest(){
		foreach ($this->controllers as $controller) {
			if ($ret = $controller->handleRequest($this->request)) {
				if ($ret instanceof Response) {
					$this->send($ret);
				}
					
				return;
			}
		}
		
		throw new NotFoundException();
	}
	
	/**
	 * @access protected
	 * @param \Rest\Exception $e
	 * @return void
	 */
	protected function handleException(Exception $e){
		foreach ($this->controllers as $controller) {
			if ($ret = $controller->handleException($e, $this->request)) {
				if ($ret instanceof Response) {
					$this->send($ret);
				}
					
				return;
			}
		}
	}
}
