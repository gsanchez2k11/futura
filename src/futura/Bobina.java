/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package futura;

import java.util.ArrayList;

/**
 *
 * @author Usuario
 */
public class Bobina extends Articulo{
    ArrayList<PrecioMedida> preciomedida;

    public Bobina(Fabricante marca, String nombre, ArrayList preciomedida) {
        super(marca, nombre);
        this.preciomedida = preciomedida;
    }
    
}
