# Naran Boilerplate Code

WordPress 플러그인 개발을 위한 상용구 코드 (boilerplate code). 여러 플러그인 코드를 개발하면서 겪은 공통된 경험을 모아 플러그인의 기초 구조를 제공합니다.

## 시작하기

### 요구사항 확인

* WordPress 5.x 버전.
* PHP 7.4 이상.

### 설치

composer 를 통해 간단하게 설치할 수 있습니다. 아래 예제는 `wp-content/plugin`에서
'boilerplate-code' 디렉토리를 만들고 그 안에 상용구 코드를 설치합니다.

```
composer create-project --no-dev naran/boilerplate-code 
```

받아진 패키지는 그대로 새로운 플러그인의 뼈대 코드로 동작합니다. 그러나 다른 플러그인과의 충돌을 막기 위해 WordPress 기본 방식인 접두어(prefix)를 일괄 변경해야 합니다.
`compoer create-project` 명령 중 자동으로 실행됩니다.

차후 별도로 실행할 때는 `wp-content/plugin/boilerplate-code/bin/prefix-change.php`
스크립트를 CLI에서 실행하면 됩니다.

```
$ cd boilerplate-code
$ php bin/prefix-changer.php myplugin # 접두 npbc를 myplugin 으로 변경.
```

접두어는 다음 규칙을 따릅니다.

1. 입력시 영소문자, 숫자, 대시(-), 밑줄(_)만 사용할 수 있습니다.
2. 접두어 첫글자는 영소문자만 가능합니다.
3. 대시나 밑줄은 두번이상 연속으로 사용해서는 안됩니다.
4. 접두어는 대시나 밑줄로 끝나서는 안됩니다.
5. 새로운 접두어는 'nbpc'나 'cpbn'이라는 단어를 포함하면 안됩니다.

접두어는 코드나 파일 이름 치환시 다음처럼 사용됟니다. 
1. 클래스 이름과 상수를 위해 영대문자와 밑줄을 사용합니다. 대시는 밑줄로 치환됩니다. (예: ABC_DEF)
2. 함수나 일반적인 문자열에서는 영소문자와 밑줄을 사용합니다. 대시는 밑줄로 치환됩니다. (예: abc_def)
3. 접두어만 정확하게 홑따옴표나 쌍따옴표로 감싼 문자열에 대해서는 영소문자와 대시를 사용합니다. 밑줄은 대시로 치환됩니다. (예 'abc-def') 


### 버전 변경
플러그인 메인 헤더 파일의 'version' 태그 정보를 기준으로 정의된 버전 문자열을 동기화할 수 있습니다.
예를 들어, 메인의 헤더 파일이 아래처럼 되어 있다면,

```php
<?php
/*
 * Plugin Name: Sample
 * Description: Sample plugin.
 * Version:     1.1.0
 */

// 이하 생략
```

플러그인의 버전은 1.1.0입니다. 그런데 이에 맞춰 `package.json`, `composer.json`이나 메인 파일 자체에 있는 상수 선언도 
이에 맞춰 같은 문자열을 가져야 합니다. 이 때, `bin/sync-version.php` 스크립트를 사용할 수 있습니다.

```
$ php bin/sync-version.php index.php # 메인 파일을 정확히 지정.
* Target version: 1.1.0
...
```

`composer.json`에 `version` 명령어로 기본 등록이 되어 있습니다.

```
$ composer version
* Target version: 1.1.0
...
```
