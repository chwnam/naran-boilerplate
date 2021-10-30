# Naran Boilerplate Code

WordPress 플러그인 개발을 위한 상용구 코드 (boilerplate code).
여러 플러그인 코드를 개발하면서 겪은 공통된 경험을 모아 플러그인의 기초 구조를 제공합니다.

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

### 접두어 일괄 변경
받아진 패키지는 그대로 새로운 플러그인의 뼈대 코드로 동작합니다. 그러나 다른 플러그인과의 충돌을 막기 위해
WordPress 기본 방식인 접두어(prefix)를 일괄 변경해야 합니다.

먼저 플러그인의 최상위 디렉토리에서 `.prefix-change.lock` 파일을 발견하면, 이것을 제거하세요.
`prefix-change.php` 스크립트가 의도하지 않게 파일 이름과 소스를 변경하지 않게 하기 위한 장치입니다. 

`wp-content/plugin/boilerplate-code/bin` 디렉토리에 `prefix-change.php` 스크립트가 있습니다. 이것을 CLI에서 실행합니다.

```
$ cd boilerplate-code
$ rm .prefix-change.lock              # 잠금 해제.
$ php bin/prefix-changer.php myplugin # 접두 npbc를 myplugin 으로 변경.
$ touch .prefix-change.lock           # 잠금.
```

접두어는 다음 규칙을 따릅니다.

* 입력시 영소문자, 숫자만 사용할 수 있습니다.
* 접두어는 define 클래스 이름과 상수를 위해 모두 영대문자로 변경됩니다.
* 파일 이름, 함수 이름, 그리고 문자열을 위해서는 영소문자 그대로 사용됩니다.


### 기타 파일 변경
prefix-changer.php 스크립트는 `includes` 디렉토리와 메인 파일인 `index.php`만을 대상으로 동작합니다.
이외의 파일은 필요에 맞게 적절히 변경하시기 바랍니다.
