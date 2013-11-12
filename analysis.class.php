<?php
class Analysis
{
	public $subfolders = FALSE;

	public $source;
	public $output;

	private $_datalist = array();
	private $_datasort = array();

	private $_datacomp = array();

	public function __construct($source=NULL, $output=NULL)
	{
		$this->source($source);
		$this->output($output);
	}

	public function source($folder=NULL)
	{
		if (isset($folder)) $this->source = $folder;
		return $this->source;
	}

	public function output($folder=NULL)
	{
		if (isset($folder)) $this->output = $folder;
		return $this->output;
	}

	public function process()
	{
		$this->_msg("Processing...");
		$data = $this->_getDataList($this->source);

		$output  = array();
		$output2 = array();
		$comps   = array();

		foreach($data as $element=>$elementdata) {
			foreach($elementdata as $orientation=>$orientationdata) {
				ksort($orientationdata);
				$output[$element][$orientation][] = "Temperature,EPC,EVC,BBE,SBE,VFE";
				foreach($orientationdata as $temperature=>$temperaturedata) {
					$stats   = array();

					foreach($temperaturedata as $row=>$rowdata) {
						$calculate = ($row > 1); // Ignore first 2 in averages calculation
						if (isset($rowdata['nul'])) $EPC = trim($rowdata['nul']); else {$EPC = "0"; $calculate = FALSE;}
						if (isset($rowdata['psm'])) $SBE = trim($rowdata['psm']); else {$SBE = "0"; $calculate = FALSE;}
						if (isset($rowdata['vac'])) $EVC = trim($rowdata['vac']); else {$EVC = "0"; $calculate = FALSE;}

						$BBE = ($calculate && $EPC && $EVC) ? $EPC - $EVC : "0";
            			$VFE = ($calculate && $BBE && $SBE) ? $BBE - $SBE : "0";
            			if ($calculate && $BBE) $stats['BBE'][] = $BBE;
            			if ($calculate && $SBE) $stats['SBE'][] = $SBE;
            			if ($calculate && $VFE) $stats['VFE'][] = $VFE;
            			$output[$element][$orientation][] = "{$temperature},{$EPC},{$EVC},{$BBE},{$SBE},{$VFE}";


					}

					#echo $this->_dump("{$element} >> {$temperature} >> {$orientation}");
					#echo $this->_dump($stats['VFE']);
					#echo $this->_dump();

					$avg_BBE = (isset($stats['BBE'])) ? $this->_average($stats['BBE']) : "0";
					$avg_SBE = (isset($stats['SBE'])) ? $this->_average($stats['SBE']) : "0";
					$avg_VFE = (isset($stats['VFE'])) ? $this->_average($stats['VFE']) : "0";
					$deviant = (isset($stats['VFE'])) ? $this->_standard_deviation($stats['VFE']) : "0";
					$output[$element][$orientation][] = ",,,{$avg_BBE},{$avg_SBE},{$avg_VFE}";
					$output[$element][$orientation][] = ",,,,,{$deviant}";

					// Second output file
					$comps[$element][$temperature][$orientation]['VFE'] = -$avg_VFE;
					$comps[$element][$temperature][$orientation]['BBE'] = -$avg_BBE;
					$comps[$element][$temperature][$orientation]['SBE'] = -$avg_SBE;
					$comps[$element][$temperature][$orientation]['sdv'] = $deviant;
				}
			}
		}

		#echo $this->_dump($comps);
		
		// Second output file
		error_reporting(0);
		foreach($comps as $element=>$temperaturedata) {
			$output2[$element][] = "Ev,110,err,100,err,111,err,000,err,,Ebulk,110,100,111,000,,Esurf,110,100,111,000";
			foreach($temperaturedata as $temperature=>$energydata) {
				$rowdata  = "{$temperature},{$energydata[110]['VFE']},{$energydata[110]['sdv']},{$energydata[100]['VFE']},{$energydata[100]['sdv']},{$energydata[111]['VFE']},{$energydata[111]['sdv']},{$energydata['000']['VFE']},{$energydata['000']['sdv']},,";
				$rowdata .= "{$temperature},{$energydata[110]['BBE']},{$energydata[100]['BBE']},{$energydata[111]['BBE']},{$energydata['000']['BBE']},,";
				$rowdata .= "{$temperature},{$energydata[110]['SBE']},{$energydata[100]['SBE']},{$energydata[111]['SBE']},{$energydata['000']['SBE']}";
				$output2[$element][] = $rowdata;
			}
		}
		error_reporting(-1);

		$this->_datasort = $output;
		$this->_datacomp = $output2;
		#echo $this->_dump($output2);
	}

	public function save($output=NULL) 
	{
		$this->output($output);

		echo $this->_dump($this->_datasort);

		foreach($this->_datasort as $element=>$elementdata) {
			foreach ($elementdata as $orientation=>$orientationdata) {
				#file_put_contents($this->output . "{$element}_{$orientation}.csv", implode("\n", $orientationdata));
			}
		}

		//Second output file
		echo $this->_dump($this->_datacomp);

		foreach($this->_datacomp as $element=>$elementdata) {
			#file_put_contents($this->output . "{$element}_vac_form_e.csv", implode("\n", $elementdata));
		}

	}

	private function _getDataList($source)
	{
		$result = array();

		$this->_msg("Entering folder " . $source);

		if ($handler = opendir($source)){
			while (($filename = readdir($handler)) !== FALSE){
			    if (strpos($filename, '.txt') !== FALSE){
			    	$this->_getDataFile($source.$filename);
			    } else if ($this->subfolders) {
			    	if (($filename != ".") && ($filename != "..") && is_dir($filename)) {
			    		$result = $this->_getDataList($filename);
			    	}
			    }

			}
		}

		#$this->_dump($this->_datalist);

		return $this->_datalist;
	}

	private function _getDataFile($file)
	{
		$this->_msg("Working with " . $file);

		@list($element, $orientation, $temperature, $suffix) = explode("_", pathinfo($file, PATHINFO_FILENAME));
		if (!isset($suffix)) $suffix = "nul";
		$temp = (int)$temperature;

		if ($contents = file($file)) {
			foreach($contents as $key=>$value) {
				$this->_datalist[$element][$orientation][$temp][$key][$suffix] = $value;
			}
		}
	}

	private function _average($array)
	{
		return array_sum($array) / count($array);
	}

	private function _standard_deviation($array, $sample=FALSE)
	{
		$mean = array_sum($array) / count($array);
	    $variance = 0.0;
	    foreach ($array as $i){
	        $variance += pow($i - $mean, 2);
	    }
	    $variance /= ($sample ? count($array) - 1 : count($array));
	    
	    return (float)sqrt($variance);
	}

	private function _msg($message)
	{
		echo $message . "<br />";
	}

	private function _dump($variable)
	{
		print("<pre>");
		print_r($variable);
		print("</pre>");
	}
}