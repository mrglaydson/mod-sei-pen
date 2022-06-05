
/**
 * Função de sinalização de pendência no arquivo da unidade
 * 
 * @param {boolean} notificar Informação se há pendência na unidade
 * @param {int} quantidade Quantidade de processo pendentes na unidade
 */
 function notificarPendencias(notificar, quantidade){
    let itemMenu = document.querySelector("li > a[link='gd_arquivamento_listar']");
     if(notificar){
        itemMenu.style.position = 'relative';

        let div = document.createElement("div");

        if (quantidade < 10) {
            div.className = "pendencia";
        } else if (quantidade > 9) {
            div.className = "pendenciaDoisDigitos pendencia";
        } else if (quantidade > 99) {
            div.className = "pendenciaMaisDigitos pendencia";
        } 
          
        div.id = 'divPendenciaArquivo';

        let span = document.createElement("span");

        span.style.setProperty("color", "white", "important");
        span.style.fontWeight = "bold";
        span.style.fontSize = "12px";

        if (quantidade < 1000) {
            span.innerHTML = quantidade;
        }else{
            span.innerHTML = "999+";
            let div2 = document.createElement("div");
                div2.setAttribute("role", "tooltip");
                div2.setAttribute("info", "info");
                div2.setAttribute("place", "right");
                div2.className = "br-tooltip";

            let span2 = document.createElement("span");
                span2.setAttribute("role", "tooltip");
                span2.className = "text";
                span2.innerHTML = quantidade;

                div2.appendChild(span2);
                span.appendChild(div2);

        }
        
        div.appendChild(span);
        itemMenu.appendChild(div);

    } else {
        let span = document.querySelector("li > a[link='gd_arquivamento_listar'] #divPendenciaArquivo");
        itemMenu.removeChild(div);
    }
}