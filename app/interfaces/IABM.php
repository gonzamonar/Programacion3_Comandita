<?php
interface IABM
{
	public function FetchOne($request, $response, $args);
	public function FetchAll($request, $response, $args);
	public function CreateOne($request, $response, $args);
	public function DeleteOne($request, $response, $args);
	public function UpdateOne($request, $response, $args);
}
