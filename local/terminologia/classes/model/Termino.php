<?php

namespace LocalPages;

use stdClass;

require(__DIR__ . '/../../../../config.php');

class Termino {

	public function __construct() {
	}

	private function getOneWeekAgo() {
		return strtotime('-1 week');
	}

	/**
	 * Get Termino element by letter
	 *
	 * @param string $letra
	 * @return array
	 */
	public function ObtenerTerminoPorLetra($letra) {
		global $DB;
		$data = $DB->get_records_sql("SELECT * FROM {termino} WHERE nombre LIKE ?", array($letra."%"));

		$data = !empty($data) ? $data : array();

		return $data;
	}

	/**
	 * Get Termino element by id
	 *
	 * @param int $id
	 * @return array
	 */
	public function ObtenerTerminoPorId($id) {
		global $DB;
		$data = $DB->get_record_sql("SELECT * FROM {termino} WHERE id = ?", array($id));

		$data = !empty($data) ? $data : array();

		return $data;
	}

	private function saveImage($filesArr) {
		/* Getting file name */
		$filename = $filesArr['files']['name'];
		/* Location */
		$location = "upload/".$filename;

		$uploadOk = 1;
		$imageFileType = pathinfo($location,PATHINFO_EXTENSION);

		/* Valid Extensions */
		$valid_extensions = array("jpg","jpeg","png");
		/* Check file extension */
		if( !in_array(strtolower($imageFileType), $valid_extensions) ) {
			$uploadOk = 0;
		}

		if($uploadOk == 0){
			echo 0;
		}else{
			/* Upload file */
			$fullPathLocation = dirname(__DIR__, 2) .'/' . $location;

			if(!move_uploaded_file($filesArr['files']['tmp_name'], $fullPathLocation)){
				echo 0;
			}
		}

		return $location;
	}

	/**
	 * Add Termino
	 *
	 * @param $params
	 * @param array $filesArr
	 * @return bool
	 * @throws \dml_exception
	 */
	public function AgregarTermino($params, $filesArr) {
		global $DB;

		$termino = new stdClass();
		$termino->nombre = $params['termino-nombre'];
		$termino->origen = $params['termino-origen'];
		$termino->descripcion = $params['termino-descripcion'];
		$termino->creado = time();

		if(!empty($filesArr['files']['name'])) {
			$termino->imageurl = $this->saveImage($filesArr);
		}

		return $DB->insert_record('termino', $termino);
	}

	/**
	 * Edit Termino
	 *
	 * @param array $params
	 * @param array $filesArr
	 * @return bool
	 * @throws \dml_exception
	 */
	public function EditarTermino($params, $filesArr) {
		global $DB;

		$termino = new stdClass();
		$termino->id = $params['termino-id'];
		$termino->nombre = $params['termino-nombre'];
		$termino->origen = $params['termino-origen'];
		$termino->descripcion = $params['termino-descripcion'];

		if(!empty($filesArr['files']['name'])) {
			$termino->imageurl = $this->saveImage($filesArr);
		} else {
			if($params['uploaded-image'] == 0) {
				$termino->imageurl = '';
			}
		}

		return $DB->update_record('termino', $termino);
	}

	/**
	 * Eliminar Termino
	 *
	 * @param integer $id
	 * @return bool
	 */
	public function EliminarTermino($id) {
		global $DB;

		return $DB->delete_records('termino', ['id' => $id]);
	}

	/**
	 * Get Sugerencias
	 */
	public function GetSugerencias() {
		global $DB;
		$data = $DB->get_records_sql("SELECT * FROM {termino_sugerido}");
		$data = !empty($data) ? $data : array();
		return $data;
	}

	/**
	 * Add Termino Sugerido
	 *
	 * @param string $terminoSugerido
	 * @return bool
	 */
	public function AgregarSugerencia($terminoSugerido)
	{
		global $DB;

		$terminoSugeridoObj = $DB->get_record_sql("SELECT * FROM {termino_sugerido} WHERE nombre = ?", array($terminoSugerido));

		if ($terminoSugeridoObj) {
			$terminoSugeridoObj->cantidad++;
			$terminoSugeridoObj->creado = time();
			return $DB->update_record('termino_sugerido', $terminoSugeridoObj);
		} else {
			$terminoSugeridoInsert = new stdClass();
			$terminoSugeridoInsert->nombre = $terminoSugerido;
			$terminoSugeridoInsert->creado = time();
			return $DB->insert_record('termino_sugerido', $terminoSugeridoInsert);
		}
	}

	/**
	 * Eliminar Sugerencia
	 */
	public function EliminarSugerencia($id) {
		global $DB;
		return $DB->delete_records('termino_sugerido', ['id' => $id]);
	}

	/**
	 * Get mas buscados
	 */
	public function GetMasBuscados() {
		global $DB;
		$data = $DB->get_records_sql("SELECT id,nombre,origen FROM {termino} ORDER BY visitas DESC");
		$data = !empty($data) ? array_slice($data,0,10) : array();
		return $data;
	}

	/**
	 * Get termino busqueda
	 * @param string $keyword
	 * @return array
	 * @throws \dml_exception
	 */
	public function GetTerminoBusqueda($keyword) {
		global $DB;
		$data = $DB->get_records_sql("SELECT * FROM {termino}  WHERE nombre LIKE ?", array($keyword."%"));
		$data = !empty($data) ? $data : array();
		return $data;
	}

	/**
	 * Add termino visita
	 * @param integer $id
	 * @return array
	 */
	public function AgregarTerminoVisita($id) {
		global $DB;

		$terminoBuscadoInsert = new stdClass();
		$terminoBuscadoInsert->id_termino = $id;
		$terminoBuscadoInsert->creado = time();

		$DB->insert_record('termino_buscado', $terminoBuscadoInsert);

		$terminoObj = $DB->get_record_sql("SELECT * FROM {termino} WHERE id = ?", array($id));

		if (!empty($terminoObj)) {
			$terminoObj->visitas++;
			return $DB->update_record('termino', $terminoObj);
		}

		return null;
	}

	private function getPalabrasCount($queryArr) {
		$data3 = array();
		$currentTerminoID = array();

		foreach($queryArr as $key=>$d2) {
			if(in_array($d2->id_termino, $currentTerminoID)) {
				continue;
			}
			$currentTerminoID[] = $d2->id_termino;
			if($d2->creado < $this->getOneWeekAgo()) {
				continue;
			} else {
				$data3[$key] = $d2;
			}
		}

		return count($data3);
	}

	private function getUsuariosActivosCount($queryArr) {
		$data3 = array();
		$currentUsuarioID = array();

		foreach($queryArr as $key=>$d2) {
			if(in_array($d2->id_usuario, $currentUsuarioID)) {
				continue;
			}
			$currentUsuarioID[] = $d2->id_usuario;
			if($d2->creado < $this->getOneWeekAgo()) {
				continue;
			} else {
				$data3[$key] = $d2;
			}
		}

		return count($data3);
	}

	private function getNuevosTerminosCount($queryArr) {
		$data3 = array();

		foreach($queryArr as $key=>$d2) {
			if($d2->creado < $this->getOneWeekAgo()) {
				continue;
			} else {
				$data3[$key] = $d2;
			}
		}

		return count($data3);
	}

	/**
	 * @return integer
	 */
	public function GetNuevosTerminos() {
		global $DB;
		$data = $DB->get_records_sql("SELECT * FROM {termino}");
		return $this->getNuevosTerminosCount($data);
	}

	/**
	 * @return integer
	 */
	public function GetPalabrasBuscadas() {
		global $DB;
		$data = $DB->get_records_sql("SELECT * FROM {termino_buscado}");
		return $this->getPalabrasCount($data);
	}

	/**
	 * @param integer $idUsuario
	 * @return integer
	 */
	public function AgregarUsuarioActivo($idUsuario) {
		global $DB;
		$usuarioActivoInsert = new stdClass();
		$usuarioActivoInsert->id_usuario = $idUsuario;
		$usuarioActivoInsert->creado = time();

		return $DB->insert_record('termino_usuario_log', $usuarioActivoInsert);
	}

	/**
	 * @return integer
	 */
	public function GetUsuariosActivos() {
		global $DB;
		$data = $DB->get_records_sql("SELECT * FROM {termino_usuario_log}");
		return $this->getUsuariosActivosCount($data);
	}


}