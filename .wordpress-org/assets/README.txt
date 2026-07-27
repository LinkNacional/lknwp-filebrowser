Este diretório contém os assets para o repositório WordPress.org (SVN ou GitHub deploy).

Copie os arquivos de includes/assets/screenshots/ para cá com os seguintes nomes:

  pagina-inicial-de-configuração.png          → screenshot-1.png
  selecionando-arquivos.png                   → screenshot-2.png
  componenete-frontend.png                    → screenshot-3.png
  selecionando-arquivos-frontend.png           → screenshot-4.png
  selecionando-arquivos-modo-lista-frontend.png → screenshot-5.png
  modo-mobile.png                             → screenshot-6.png
  como-usar.png                               → screenshot-7.png

Comando rápido (rode na raiz do plugin):

  mkdir -p .wordpress-org/assets
  SRC="includes/assets/screenshots"
  DST=".wordpress-org/assets"
  cp "$SRC/pagina-inicial-de-configuração.png" "$DST/screenshot-1.png"
  cp "$SRC/selecionando-arquivos.png" "$DST/screenshot-2.png"
  cp "$SRC/componenete-frontend.png" "$DST/screenshot-3.png"
  cp "$SRC/selecionando-arquivos-frontend.png" "$DST/screenshot-4.png"
  cp "$SRC/selecionando-arquivos-modo-lista-frontend.png" "$DST/screenshot-5.png"
  cp "$SRC/modo-mobile.png" "$DST/screenshot-6.png"
  cp "$SRC/como-usar.png" "$DST/screenshot-7.png"
