<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>


# Teste Go Sat

**Descrição:** O teste tem como objetivo validar os conhecimentos técnicos em PHP + Laravel;

**Pessoa avaliada:** [Maria Baeta](https://github.com/mariabaeta27);

**Stacks:** PHP, Laravel. SQlite, Swagger;

**Desafio:** Desenvolver uma API para consultar a disponibilidade de crédito para um determinado CPF e informar qual é a melhor oportunidade a ser ofertada.
<br/>
*Observação: Foi disponibilizado uma API para consulta dos dados necessários para realização da tarefa.*

## **Requisitos:**

- Obrigatórios:
  - [x] Utilizar o Swagger, Postman ou alguma ferramenta similar para documentar suas rotas;
  - [x] Entregue uma documentação com todos os passos para executar seu projeto;
  - [x] Utilizar banco de dados para persistir os dados gerados durante o teste;
  - [x] Crie uma rota (endpoint) para receber qual CPF será consultado.

    ```bash
      CPFs Disponíveis:
      111.111.111-11
      123.123.123.12
      222.222.222.22
    ```

  - [ ] Desenvolver algum relatório gráfico fazendo uso linguagens e ferramentas Front-end;

- Opcionais:
  - [ ] Disponibilizar o teste na internet, para que possa ser testado via navegador ou Postman.

## Retorno da api:

Selecione até 3 ofertas de crédito e ordene-as da mais vantajosa a menos vantajosa para o cliente. Para cada oferta de crédito selecionada deve-se aplicar lógicas (manipulação de estruturas de dados, realização de cálculos, etc) de forma que a oferta contenha as seguintes informações:

 ```bash
    instituicaoFinanceira;
    modalidadeCredito;
    valorAPagar;
    valorSolicitado;
    taxaJuros;
    qntParcelas
  ```

## Setup dp projeto

- **Importante:**

- Baixe o repositorio [Git](https://github.com/mariabaeta27/api-go-sat)
- Acesso a pasta do projeto

### Instale as dependências

```bash

composer install

```

### Crie um arquivo de configuração de ambiente e gere a chave do aplicativo

```bash

cp .env.example .env
php artisan key:generate

```

### Crie o banco de dados

```bash

php artisan migrate

```

### Inicie o servidor

```bash

php artisan serve

```

### Rotas disponíveis: 

```bash

Metodo:       Rota: 

  POST        api/simulation (Criação de simulação)
  GET|HEAD    api/simulation (Lista de simulações)
  GET|HEAD    api/documentation (Documentação das rotas no Swagger)

```
