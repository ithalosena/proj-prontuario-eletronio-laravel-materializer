<p align="center" text ><a href="" target="_blank"><img src="https://github.com/ithalosena/proj-prontuario-eletronio-laravel-materializer/blob/main/public/assets/img/branding/logo-text.png?raw=true" width="400" alt="Prontu IF Logo"></a></p>

# **Prontu IF - Sistema de Prontuário Eletrônico para o IFNMG**

## **Descrição**

O **Prontu IF** é um sistema de prontuário eletrônico web desenvolvido
para o Instituto Federal do Norte de Minas Gerais (IFNMG), com o
objetivo de otimizar o registro e a gestão das informações de saúde dos
alunos, facilitando o trabalho dos profissionais e melhorando a
qualidade do atendimento.

O sistema oferece funcionalidades para o cadastro de usuários
(administradores e profissionais de saúde), pacientes (alunos), registro
de consultas, exames, prescrições, histórico de atendimento, relatórios
e estatísticas. O Prontu IF garante a segurança dos dados através de
criptografia, controle de acesso e backups regulares.

## **Funcionalidades Principais**

- **Cadastro:**

  - ✅Usuários (administradores e profissionais de saúde)

  - ✅Profissionais de Saúde (médicos, dentistas, psicólogos, etc.)

  - ✅Pacientes (alunos)

- **Prontuário Eletrônico:**

  - ⌛Registro de consultas

  - ⌛Registro de exames

  - ✅Prescrição de medicamentos

  - ⌛Encaminhamentos

- **Histórico de Atendimento:**

  - ⌛Visualização do histórico completo ou simplificado dos atendimentos

- **Relatórios e Estatísticas:**

  - ⌛Geração de relatórios personalizados

  - ⌛Visualização de estatísticas sobre a saúde dos alunos

- **Controle de Acesso e Segurança:**

  - ⌛Autenticação e autorização de usuários

  - ⌛Criptografia de dados sensíveis

  - ⌛Auditoria de ações

✅(Concluído) ⌛(Em desenvolvimento)

## **Tecnologias Utilizadas**

- **Backend:**

  - **Laravel:** Framework PHP para o desenvolvimento do backend.

  - **MySQL:** Banco de dados para armazenar as informações do sistema.

- **Frontend:**

  - **Materialize (PixInvent):** Framework CSS baseado no Material Design para a construção da interface do usuário.

## **Documentação**

- **Laravel:**

  > [[https://laravel.com/docs]{.underline}](https://laravel.com/docs)

- **Materialize (PixInvent):**
  > [[https://pixinvent.com/materialize-material-design-bootstrap-admin-template/]{.underline}](https://pixinvent.com/materialize-material-design-bootstrap-admin-template/)

## **Pré-requisitos**

- PHP \>= 8.1

- Composer

- Node.js e npm

- MySQL (ou outro banco de dados compatível com o Laravel)

## **Instalação**

1.  Clone o repositório: git clone

    > https://github.com/seu-usuario/prontu-if.git

2.  Acesse a pasta do projeto:

    > cd prontu-if

3.  Instale as dependências do PHP:

    > composer install

4.  Instale as dependências do frontend:

    > npm install

5.  Crie o arquivo .env copiando o arquivo .env.example e configure as

    > informações do seu banco de dados.

6.  Gere a chave da aplicação:

    > php artisan key:generate

7.  Execute as migrations para criar as tabelas do banco de dados: php

    > artisan migrate

8.  Compile os assets do frontend:

    > npm run dev

9.  Inicie o servidor de desenvolvimento:
    > php artisan serve

## **Utilização**

- Acesse o sistema em http://localhost:8000
  (ou em outra porta, se você tiver configurado).

- Faça o login ou registre-se como um novo usuário.

- Utilize as funcionalidades do sistema de acordo com o seu perfil de acesso.
  (administrador ou profissional de saúde).

## **Contribuição**

Contribuições são bem-vindas! Sinta-se à vontade para abrir issues ou
pull requests.

## **Sobre o Desenvolvedor**

Este projeto foi desenvolvido por Ithalo Sena e Raissa Alves como parte do Trabalho de
Conclusão de Curso (TCC) do curso de Análise e Desenvolvimento de
Sistemas (ADS) no Instituto Federal do Norte de Minas Gerais (IFNMG) e
visa contribuir para a melhoria da gestão de informações de saúde dos
alunos da instituição.

## **Sobre o IFNMG e o Curso de ADS**

O Instituto Federal do Norte de Minas Gerais (IFNMG) é uma instituição
de ensino público que oferece cursos técnicos, de graduação e
pós-graduação em diversas áreas do conhecimento. O curso de Análise e
Desenvolvimento de Sistemas (ADS) do IFNMG forma profissionais
capacitados para projetar, desenvolver, implantar e manter sistemas de
informação, utilizando tecnologias e metodologias adequadas.

## **Licença**

Este projeto está licenciado sob a licença MIT. Consulte o arquivo
LICENSE para obter mais detalhes.

**Contato**

- Nome do Desenvolvedor: \[Ithalo Silva Sena Aquino\]

- E-mail: \[ithalosena@gmail.com\]

Sujeito à licença (MIT)
