//function abrir() {
/*
FILE = new ActiveXObject("Scripting.FileSystemObject");

//abrir um arquivo
fopen = FILE.OpenTextFile("teste.txt",1);
//criar um arquivo
fw = FILE.CreateTextFile("temp.txt");
*/
//alert("arquivo: ");

//}
//permissão 1 para leitura, 2 para gravação e 8 para leitura em fim de linha 

function salvar(conteudo,nomearquivo) {
	realca(3);
    document.getElementById('conteudo').document.designMode = 'On';
    
    frames['conteudo'].document.body.innerHTML = conteudo;
    
    frames['conteudo'].document.execCommand('SaveAs',false,nomearquivo);
}