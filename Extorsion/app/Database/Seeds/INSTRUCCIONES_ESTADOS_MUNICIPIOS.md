# Carga de estados y municipios

## Archivo que se edita

Usa este archivo:

```text
app/Database/Seeds/estados_municipios.csv
```

El formato debe ser:

```csv
estado,municipio
Estado de Mexico,Nezahualcoyotl
Estado de Mexico,Ecatepec de Morelos
Jalisco,Zapopan
```

## Pasos

1. Descarga una lista de municipios en CSV.
2. Deja solo dos columnas: `estado` y `municipio`.
3. Copia esos datos en `app/Database/Seeds/estados_municipios.csv`.
4. Guarda el archivo como CSV.
5. Ejecuta este comando desde la raiz del proyecto:

```powershell
php spark db:seed EstadosMunicipiosSeeder
```

## Importante

- El Seeder no duplica estados ni municipios existentes.
- Si un estado no existe, lo crea.
- Si un municipio no existe dentro de ese estado, lo crea.
- Si ejecutas el comando dos veces con el mismo CSV, la segunda vez debe insertar `0`.

## Fuente recomendada

Puedes usar un catalogo publico en CSV de municipios de Mexico, por ejemplo el recurso de datos.gob.mx:

```text
https://www.datos.gob.mx/es/dataset/catalogo_municipios
```

Tambien puedes usar el catalogo oficial de INEGI si necesitas el corte mas formal:

```text
https://www.inegi.org.mx/app/ageeml/
```
