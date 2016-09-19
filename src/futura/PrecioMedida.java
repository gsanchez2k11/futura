/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package futura;

/**
 *
 * @author Usuario
 */
class PrecioMedida {
    String ref;
    int unidades;
    double ancho;
    double largo;
    double precioUd;

    public PrecioMedida(String ref, int unidades, double ancho, double largo, double precioUd) {
        this.ref = ref;
        this.unidades = unidades;
        this.ancho = ancho;
        this.largo = largo;
        this.precioUd = precioUd;
    }
    
    
}
