<?php
//1D Not used
function binify($xx, $min, $max, $size) {
  $n=count($xx);
  $m=intval(($max-$min)/$size);
  $yy = array_fill(0, $m, 0);
  $i_m=intdiv($min,$size);
  $i_M=intdiv($max,$size);
  for ($i = 0; $i < $n ; $i++) {
    $i_x=intdiv($xx[$i],$size)-$i_m;
    if ($i_x < $m) {
      $yy[$i_x]++;
    }
  }
  return $yy;
}

function binify2d($xx, $yy, $xmin, $xmax, $xsize, $ymin, $ymax, $ysize) {
  $n=count($xx);
  $mx=intval(($xmax-$xmin)/$xsize);
  $my=intval(($ymax-$ymin)/$ysize);
  $zz = array_fill(0, $mx, array_fill(0, $my, 0));
  $i_mx=intdiv($xmin,$xsize);
  $i_Mx=intdiv($xmax,$xsize);
  $i_my=intdiv($ymin,$ysize);
  $i_My=intdiv($ymax,$ysize);
  for ($i = 0; $i < $n ; $i++) {
    $i_x=intdiv($xx[$i],$xsize)-$i_mx;
    $i_y=intdiv($yy[$i],$ysize)-$i_my;
    if (($i_x < $mx) & ($i_y < $my)) {
      $zz[$i_x][$i_y]++;
    }
  }
  return $zz;
}

function compress2d($matrix){
  $N=count($matrix);
  $M=count($matrix[0]);
  $x=array();
  $y=array();
  $x=array();
  //echo "matrix size ".$N." x ".$M."<br>";
  for ($i = 0; $i < $N ; $i++) {
    for ($j = 0; $j < $M ; $j++) {
      if ($matrix[$i][$j]){
        $x[]=$i;
        $y[]=$j;
        $z[]=$matrix[$i][$j];
      }
    }
  }
  $sizeI=$N*$M;
  $sizeO=count($x)*3;
  //echo "IN: ". $sizeI . " OUT: ". $sizeO . "<br>";
  return [$x,$y,$z];
}

function decompress2d($x, $y, $z, $N, $M){
  $zz = array_fill(0, $N, array_fill(0, $M, 0));
  for ($i = 0; $i < count($x) ; $i++) {
    $ii=$x[$i];
    $jj=$y[$i];
    $zz[$ii][$jj]=$z[$i];
    }
  return $zz;
}

?>
