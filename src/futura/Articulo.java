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
public abstract class Articulo {
    Fabricante marca;
    String nombre;
    
    public Articulo(Fabricante marca, String nombre) {
        this.marca = marca;
        this.nombre = nombre;
    }
    
}
