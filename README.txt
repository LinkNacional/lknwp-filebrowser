=== Link Nacional File Browser ===
Contributors: linknacional
Tags: file-browser, file-manager, documents, upload, folders
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Um plugin completo de navegador de arquivos com sistema de pastas hierárquicas e interface de pesquisa para WordPress.

== Description ==

O Link Nacional File Browser é um plugin WordPress que permite criar e gerenciar um sistema de arquivos hierárquico completo. Oferece uma interface administrativa para organizar arquivos em pastas e um shortcode para exibir o navegador de arquivos no frontend.

**Principais Recursos:**

* **Sistema de Pastas Hierárquicas**: Crie pastas e subpastas organizadas como no Windows Explorer
* **Upload de Múltiplos Arquivos**: Suporte para PDF, Word, Excel, PowerPoint, imagens e mais
* **Interface de Pesquisa**: Busca rápida por arquivos e pastas no frontend
* **Visualização Responsiva**: Layout em grade ou lista adaptável a dispositivos móveis
* **Breadcrumb Navigation**: Navegação intuitiva entre pastas
* **Shortcode Flexível**: Múltiplas opções de configuração via shortcode

**Tipos de Arquivo Suportados:**
* Documentos: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT
* Imagens: JPG, JPEG, PNG, GIF

== Installation ==

1. Faça upload do plugin para o diretório `/wp-content/plugins/lknwp-filebrowser/`
2. Ative o plugin através do menu 'Plugins' no WordPress
3. O plugin criará automaticamente as tabelas necessárias no banco de dados
4. Acesse 'File Browser' no menu administrativo para começar a organizar seus arquivos

== Frequently Asked Questions ==

= Como usar o shortcode? =

O plugin inclui um painel de instruções no admin com exemplos práticos e botões para copiar shortcodes.

Acesse 'File Browser' no menu administrativo para ver as instruções completas de uso.

**Uso básico:**
`[lknwp_filebrowser]`

**Pasta específica:**
`[lknwp_filebrowser folder_id="1"]`

**Layout diferente:**
`[lknwp_filebrowser layout="list"]`

**Layouts disponíveis:** grid (padrão) e list

= Onde encontro instruções de uso? =

No painel administrativo do plugin há um bloco de instruções com:
* Exemplos de shortcodes
* Botões para copiar shortcodes
* Dicas de uso
* Guia de funcionalidades

= Como organizar arquivos em pastas? =

1. Acesse 'File Browser' no menu administrativo
2. Veja as instruções na parte superior da página
3. Clique em 'Create Folder' para criar uma nova pasta
4. Navegue até a pasta desejada e clique em 'Upload Files'
5. Selecione múltiplos arquivos para upload simultâneo

= Como os usuários podem acessar os arquivos? =

Os usuários podem:
* Navegar pelas pastas clicando nelas
* Usar a barra de pesquisa para encontrar arquivos específicos
* Baixar arquivos clicando neles
* Alternar entre visualização em grade e lista

= O plugin é responsivo? =

Sim! O plugin foi desenvolvido com design responsivo, adaptando-se automaticamente a tablets e smartphones.

== Screenshots ==

1. Interface administrativa - Gerenciamento de pastas e arquivos
2. Visualização em grade no frontend
3. Visualização em lista no frontend
4. Funcionalidade de pesquisa
5. Interface mobile responsiva

== Changelog ==

= 1.0.0 - 12/09/2025 =
* Ajuste na documentação. 

= 1.0.0 =
* Versão inicial
* Sistema completo de pastas hierárquicas
* Upload de múltiplos arquivos
* Interface de pesquisa
* Shortcode flexível com múltiplas opções
* Design responsivo
* Suporte para múltiplos tipos de arquivo

== Technical Details ==

**Estrutura do Banco de Dados:**
* `wp_lknwp_filebrowser_folders`: Armazena informações das pastas
* `wp_lknwp_filebrowser_files`: Armazena informações dos arquivos

**Diretório de Upload:**
Os arquivos são armazenados em `/wp-content/uploads/lknwp-filebrowser/`

**Hooks Disponíveis:**
* `lknwp_filebrowser_before_upload`: Executado antes do upload
* `lknwp_filebrowser_after_upload`: Executado após o upload
* `lknwp_filebrowser_before_delete`: Executado antes da exclusão

== Support ==

Para suporte técnico, entre em contato:
* Email: contato@linknacional.com
* Website: https://www.linknacional.com.br

== License ==

Este plugin é licenciado sob GPL v2 ou posterior.

== Requirements ==

* WordPress 5.0 ou superior
* PHP 7.4 ou superior
* MySQL 5.6 ou superior
