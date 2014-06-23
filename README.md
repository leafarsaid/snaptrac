snaptrac
========

Sistema de Interpretação de Coordenadas Geográficas

A essência deste software consiste na aquisição de informações relativas a dados geográficos de determinados pontos, com base no processamento de dois arquivos, sendo uma planilha com as coordenadas desses pontos e um arquivo com a informação de trilha de um determinado veículo.

As informações coletadas em cada ponto da trilha são as seguintes:

1. Latitude;
2. Longitude;
3. Data e hora;
4. Velocidade;

Com base nessas informações, é possível também conseguir informações como:

1. Número de vezes que uma trilha passou por determinado ponto;
2. Todas as informações geográficas de cada vez que a trilha passou por esses pontos;
3. Ocorrências de velocidade acima da máxima permitida;

Parâmetros são editados pelo arquivo snaptrac.ini e são os seguintes:

1. Velocidade máxima;
2. Fuso-horário;
3. Gate;
4. Local do arquivo de pontos;

O processo é feito pelo arquivo snaptrac.bat (não existe ainda) que processa três pastas:

1. Points: Pasta onde sugere-se colocar o arquivo de pontos;
2. Imports: Pasta que será lida, com os arquivos que possuam as informações de trilhas;
3. Reports: Pasta onde serão escritos relatórios, um por arquivo da pasta 'Imports';
