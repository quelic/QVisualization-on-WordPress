<?php
/*
Plugin Name: QVisual Plugin per a Wordpress
Plugin URI: http://plugin.issim.net/QVisual
Description: Visualització del contingut d'un blog mitjançant processingjs.
Version: 0.2
Author: Quelic Berga Carreras
Author URI: http://www.caotic.net
License: GPL2
*/

function posts_category() {
	global $wpdb;

	$query = "
		SELECT $wpdb->posts.post_date_gmt, $wpdb->posts.post_title, UNIX_TIMESTAMP($wpdb->posts.post_date_gmt), $wpdb->posts.comment_count, $wpdb->posts.post_content, $wpdb->terms.name FROM $wpdb->posts
		LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
		LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
		LEFT JOIN $wpdb->terms ON ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
		WHERE $wpdb->term_taxonomy.taxonomy = 'category'
		AND $wpdb->posts.post_status = 'publish'
		ORDER BY $wpdb->posts.post_date_gmt;
	";
	return $wpdb->get_results($query,ARRAY_N);
}

function categorias() {
global $wpdb;
	$query = "SELECT $wpdb->terms.name FROM $wpdb->terms
		LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
		WHERE $wpdb->term_taxonomy.taxonomy = 'category'
	";

	return $wpdb->get_results($query,ARRAY_N);
}

function escribe_array(){



echo '<script src="/wp-content/plugins/miprimerplugin/processing.js"></script>';
echo '<script src="/wp-content/plugins/miprimerplugin/init.js"></script>';
echo '<script type="application/processing">';
echo'
/******************************
 *  Visualització de la info d
 * un wordpress.
 *
 * 
 *
 ******************************/
 
 
 //DATOS:
 
 ';

	$myPrueba = posts_category();
	$total=count($myPrueba);
	$ii=0;
	echo "String[][] datos = {";
	for ($i=0; $i<$total; $i++) {
		echo "\n{";
		for ($j=0; $j<6;$j++) {
			if ($j == 5) { 
				if (($i<$total-1) && ($myPrueba[$i][0] == $myPrueba[$i+1][0])) {
					while (($i<$total-1) && ($myPrueba[$i][0] == $myPrueba[$i+1][0])) {
						echo '"'.$myPrueba[$i++][$j].'",';
					}
					echo '"'.$myPrueba[$i++][$j].'"'; 
				} else 	echo '"'.$myPrueba[$i][$j].'"'; 
			} else {
				if ($j == 4) {
					echo '"'.strlen($myPrueba[$i][$j]).'",'; 
			
				} else {
					echo '"'.$myPrueba[$i][$j].'",';
				}
			}
		}
		echo "}";
		$ii++;
		if ($i < ($total-1)) echo ",";
	}
	echo "};\n";
	echo "int nPosts = ".$ii."; \n";
	
	escribe_categorias();


echo'
 


//************** Inicialització general

int ample = screen.width/2;
int alt = screen.height/2;
float tamanyText = 10;

//************** Meta Dades
float[] posX = new float [datos.length];
float[] posY = new float [datos.length];
float[] pes = new float [datos.length];
String[] noms = new String [datos.length];
String[] data = new String [datos.length];
float[] temps = new float [datos.length];
int margeSup = 120; // Marge superior
int margeLat = 30; // Marge lateral
int tamanyObjecte = 100; // Tamany màxim dels cercles

void setup() {
  size(ample, alt);
  background (0);
  PFont fontA = loadFont("helv.vlw");
  textFont(fontA,10);
  noStroke();
  smooth();
  ellipseMode(CENTER);
  for (int id = 0; id < datos.length; id++) {
    temps[id] = int(datos[id][2]);
    noms[id] = datos[id][1];
    pes[id] = int(datos[id][4]);
    setupPositions();
  } 


}

void setupPositions() {

  float tempsMin = min(temps);
  float tempsMax = max(temps);
  float timeline = tempsMax-tempsMin;
  for (int id = 0; id < datos.length; id++) {
    // ordenem eix de les X segons cronología 
    posX[id] = map(temps[id], tempsMin, tempsMax, margeLat, ample-margeLat);
    for (int i = 0; i < categorias.length; i++) {
      if(datos[id][5] == categorias[i]) {   
        posY[id] = margeSup + i*((alt-margeSup)/categorias.length);
      }
    }  
  }

}


void drawCategorias () {
  fill(255,120);
  textAlign(RIGHT);
  textSize((alt-margeSup)/categorias.length);
  for (int i = 0; i < categorias.length; i++) {
    text(categorias[i], mouseX-10, margeSup + i*((alt-margeSup)/categorias.length));
  }
}


//************** Persona
void personifica (int id) {
  for (int i = 0; i < categorias.length; i++) {
    if(datos[id][5] == categorias[i]) {   
      // Color segons categoria
      fill (i*(255/categorias.length),255-(255/categorias.length)*i,i*(255/categorias.length)+255-(255/categorias.length)*i, 60);
      // Amplificació si pròxim
      if(dist(mouseX, 0, posX[id], 0) < 20) {
        // dibuix del cercle segons proximitat
        ellipse (0,  0, 5+20-dist(mouseX, 0, posX[id], 0)+tamanyObjecte*(pes[id]/max(pes)),5+20-dist(mouseX, 0, posX[id], 0)+tamanyObjecte*(pes[id]/max(pes)));
      } 
      else {
        // dibuix estàtic
        ellipse (0,  0, 5+tamanyObjecte*(pes[id]/max(pes)),5+tamanyObjecte*(pes[id]/max(pes)));
      }

    }
  }

}

void etiqueta(int id) {

  /// ETIQUETA / INFO
  if (dist(mouseX, mouseY, posX[id], posY[id])<20) {
    
    textAlign(LEFT);
    tamanyText = 20 - dist(mouseX, 0, posX[id],0); //15-(10*((dist(mouseX, mouseY, posX[id], posY[id]))/500));  
    fill(255-dist(mouseX, 0, posX[id],0));
    textSize(tamanyText);
    text(datos[id][0],20,0);

  }/* size = size/max_distance * 66;  
   float tamanyText = 13+10*(pes[id]/max(pes));
   */

}

void titol(int id) {
    fill(255,200);
   textSize(24);
   text(noms[id],nargeLat,margeSup-24-dist(mouseX, mouseY, posX[id], posY[id])*8);

}
void draw() {
}
void mouseMoved() {
  background(0);
  stroke(200,190,20, 120);

  line (mouseX, 0,mouseX, alt);
  noStroke();

  for (int id = 0; id < datos.length; id++) {
 
    pushMatrix();

    translate(posX[id], posY[id]);

    personifica (id);
    //  rotateX(-45);
    etiqueta(id);

    popMatrix();
     if (dist(mouseX, mouseY, posX[id], posY[id])<12){
       titol(id);
     }

  }
  
  drawCategorias();
}



';

echo '</script>';
echo '<canvas height="200" width="200"></canvas>';


}

function escribe_categorias(){
	$aCat=categorias();
	$total=count($aCat);
	echo "String[] categorias = {";
	for ($i=0;$i<$total;$i++) {
		echo '"'.$aCat[$i][0].'"';
		if ($i<$total-1) echo ",";
	}
	echo "};\n int nCategorias = $total;";
}
add_action ( 'get_footer' , 'escribe_array' );

?>
