Snaptrac
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
3. Ocorrências de velocidade acima da máxima permitida, separadas por 3 faixas de radar, em trechos de radar delimitados por um ponto de entrada e um de saída;

Parâmetros são editados pelo arquivo snaptrac.ini, sendo os principais atributos a serem editados:

1. Velocidade máxima;
2. Fuso-horário;
3. Gate (raio de alcance para considerar que uma coordenada passou por um ponto);
4. Local do arquivo de pontos;

O processo é feito pelo arquivo snaptrac.bat que processa três pastas:

1. Points: Pasta onde sugere-se colocar o arquivo de pontos;
2. Imports: Pasta que será lida, com os arquivos que possuam as informações de trilhas;
3. Reports: Pasta onde serão escritos relatórios, um por arquivo da pasta 'Imports';

Ao processar um arquivo, a matriz de pontos (obtida pelo processamento do arquivo de pontos) é enriquecida com a informação dos arquivos dos competidores (trilhas - Imports) com a separação feita por cada volta que o competidor der no percurso estabelecido.

A estrutura da matriz de pontos pode ser obtida pelo seguinte esquema:

* Matriz de Pontos;
	* Tipo de ponto (largada, chegada, waypoints, carimbo, inter1, inter2, inter3, inter4, entradas, saidas);
		* Ocorrência do tipo de ponto (Iniciando em 0 - ex.: ['largada'][0] - primeira largada);
			* Matriz com pontos que 'grudaram' (passagens pelos pontos - índice chamado snap);
				* Matriz que separa as passagens pelos arquivos de trilhas dos competidores;
					* Ocorrência de passagem (Iniciando em 0 - ex.: ['largada'][0]['snap']['competidor_200'][0] - Matriz com atributos do primeiro ponto que passou pela primeira largada para o competidor 200);
						* Matriz com a zona de radar de cada trecho entrada/saída (mostrado somente nas 'entradas');
							* Índices dos pontos equivalentes aos das trilhas que estão dentro da zona de radar;