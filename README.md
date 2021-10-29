# Naran Boilerplate Code

워드프레스 플러그인 개발을 위한 상용구 코드 (boilerplate code).

여러 플러그인 코드를 개발하면서 겪은 공통된 경험을 모아 상용구로 만들었습니다.
자주 사용되는 구조와 상황에 대해 반복을 줄이고 문제에 대한 가장 좋은 방법들 빠르게 대입할 수 있도록 플러그인의 기초 구조를 제공합니다.

## 시작하기

### 요구사항 확인
* WordPress 5.x 버전.
* PHP 7.4 이상.

### 설치
composer 를 통해 간단하게 설치할 수 있습니다. 아래 예제는 워드프레스의 wp-content/plugin에서
'my_plugin' 디렉토리를 만들고 그 안에 상용구 코드를 설치합니다.
```
composer create-project -s alpha changwoo/naran-boilerplate-code my_plugin
```

### 접두어 일괄 변경
받아진 패키지는 그대로 새로운 플러그인의 뼈대 코드로 동작합니다. 그러나 다른 플러그인과의 충돌을 막기 위해
워드프레스 기본 방식인 접두어(prefix)를 일괄 변경해야 합니다.

먼저 플러그인의 최상위 디렉토리에서 `.prefix-change.lock` 파일을 발견하면, 이것을 제거하세요.
`prefix-change.php` 스크립트가 의도하지 않게 파일 이름과 소스를 변경하지 않게 하기 위한 장치입니다. 

`wp-content/plugin/my_plugin/bin` 디렉토리에 `prefix-change.php` 스크립트가 있습니다. 이것을 CLI에서 실행합니다.

```
$ cd my_plugin
$ php bin/prefix-changer.php myplugin # npbc를 myplugin 으로 변경.
```

접두어는 다음 규칙을 따릅니다.

* 입력시 영소문자, 숫자만 사용할 수 있습니다.
* 접두어는 define 클래스 이름과 상수를 위해 모두 영대문자로 변경됩니다.
* 파일 이름, 함수 이름, 그리고 문자열을 위해서는 영소문자 그대로 사용됩니다.


### 기타 텍스트 파일 변경
이후 다음 파일의 내용은 완전히 변경되지 않습니다. 꼭 확인하고 필요한 부분을 변경하세요.

* 메인 파일 헤더.
* README.md
* composer.json
* .gitignore


## 기타
