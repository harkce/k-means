<?php

ini_set('max_execution_time', 300);

function loadData($filename) {
  $res = array();
  if (($handle = fopen($filename, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
      array_push($res, array(floatval($data[0]), floatval($data[1]), floatval($data[2])));
    }
    fclose($handle);
  }
  return $res;
}

function randomize($batas) {
	return ((float)rand()/(float)getrandmax()) * $batas;
}

function distance($p1, $p2) {
	return sqrt(pow($p1['x'] - $p2['x'], 2) + pow($p1['y'] - $p2['y'], 2));
}

function kmeans($dataset, $centroids, $oldsse = 100000) {
	$clustered = array();
	$datacount = count($dataset);
	$centcount = count($centroids);
	$newcentroids = array();
	for ($i=0; $i < $centcount; $i++) { 
		$centroid = array(
			'x' => 0,
			'y' => 0,
			'c' => ($i + 1),
			'count' => 0
		);
		array_push($newcentroids, $centroid);
	}
	$sse = 0;
	foreach ($dataset as $data) {
		if (isset($data->x)) {
			$data = (array) $data;
		}
		$mindist = distance($data, $centroids[0]);
		$mincls = $centroids[0]['c'];
		for ($i=1; $i < $centcount; $i++) { 
			$dist = distance($data, $centroids[$i]);
			if ($dist < $mindist) {
				$mindist = $dist;
				$mincls = $centroids[$i]['c'];
			}
		}
		$sse += $mindist;
		$newdata = array(
			'x' => $data['x'],
			'y' => $data['y'],
			'c' => $mincls
		);
		$newcentroids[$mincls - 1]['x'] += $data['x'];
		$newcentroids[$mincls - 1]['y'] += $data['y'];
		$newcentroids[$mincls - 1]['count'] += 1;
		array_push($clustered, $newdata);
	}
	$same_centroid = true;
	for ($i=0; $i < $centcount; $i++) { 
		$newcount = $newcentroids[$i]['count'];
		if ($newcount > 0) {
			$newcentroids[$i]['x'] = $newcentroids[$i]['x'] / $newcentroids[$i]['count'];
			$newcentroids[$i]['y'] = $newcentroids[$i]['y'] / $newcentroids[$i]['count'];
		}
		if ($same_centroid) {
			if ($newcentroids[$i]['x'] == $centroids[$i]['x'] && $newcentroids[$i]['y'] == $centroids[$i]['y']) {
				$same_centroid = true;
			} else {
				$same_centroid = false;
			}
		}
	}
	$clustered['sse'] = $sse;
	if ($same_centroid) {
		return $clustered;
	} elseif ($sse > $oldsse) {
		return $dataset;
	} else {
		return kmeans($clustered, $newcentroids, $sse);
	}
}

function randomize_centroid($n) {
	$centroids = array();
	for ($i=0; $i < $n; $i++) { 
		$centroid = array(
			'x' => randomize(40),
			'y' => randomize(40),
			'c' => ($i + 1)
		);
		array_push($centroids, $centroid);
	}
	return $centroids;
}

function count_class($dataset) {
	$class = array();
	foreach ($dataset as $data) {
		if (!in_array($data->c, $class)) {
			array_push($class, $data->c);
		}
	}
	return count($class);
}

function select_random_data($dataset) {
	$class = array();
	$startend = array();
	$i = 0;
	foreach ($dataset as $data) {
		if (!in_array($data->c, $class)) {
			array_push($class, $data->c);
			$startend[$data->c] = array(
				'start' => $i
			);
		} else {
			$startend[$data->c]['end'] = $i;
		}
		$i++;
	}
	$centroids = array();
	$countclass = count($class);
	for ($i=0; $i < $countclass; $i++) {
		$start = $startend[$i + 1]['start'];
		$end = $startend[$i + 1]['end'];
		$rand = rand($start, $end);
		$centroid = array(
			'x' => $dataset[$rand]->x,
			'y' => $dataset[$rand]->y,
			'c' => $dataset[$rand]->c
		);
		array_push($centroids, $centroid);
	}
	return $centroids;
}

if (isset($_POST['type'])) {
	$input = loadData('k-means/' . $_POST['filename'] . '.csv');
	$res = array();
	foreach ($input as $value) {
		$data = array(
			'x' => $value[0],
			'y' => $value[1],
			'c' => $value[2],
		);
		array_push($res, $data);
	}

	if ($_POST['type'] == 'countclass') {
		echo count_class(json_decode(json_encode($res)));
	} else {
		echo json_encode($res);
	}
}

if (isset($_POST['problem'])) {
	$dataset = (array) json_decode($_POST['dataset']);
	$centroids;
	$classnum = count_class($dataset);
	if ($_POST['problem'] == '1c') {
		$centroids = randomize_centroid($classnum);
	} elseif ($_POST['problem'] == '1f') {
		$centroids = select_random_data($dataset);
	}
	$clustered = kmeans($dataset, $centroids);
	echo json_encode($clustered);
}