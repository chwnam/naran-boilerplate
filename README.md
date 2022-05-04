# Naran Boilerplate

WordPress 플러그인 개발을 위한 상용구 코드 (boilerplate code). 여러 플러그인 코드를 개발하면서 겪은 공통된 경험을 모아 플러그인의 기초 구조를 제공합니다.

## 시작하기

### 요구사항 확인

* WordPress 5.x 버전 이상. 가급적 최신 버전을 사용하세요.
* PHP 7.4 이상.

### 설치

composer 를 통해 간단하게 설치할 수 있습니다. 아래 예제는 `wp-content/plugin`에서
'boilerplate' 디렉토리를 만들고 그 안에 상용구 코드를 설치합니다.

```
composer create-project --no-dev naran/boilerplate 
```

다른 디렉토리 이름으로 만들고 싶다면 인자를 추가하면 됩니다.

```
composer create-project --no-dev naran/boilerplate my-plugin 
```

성공적으로 받아지면 composer 는 새로운 접두어 적용을 위해 사용자에게 입력을 받습니다.

```
Please enter your new prefix (Enter 'exit' to skip): 
```

여기서 적절한 접두어를 정해 입력합니다. 만약 'foo'를 접두로 입력하면 아래처럼 확인을 받습니다. 'y'를 누르면 받아진 코드에 대해 파일 이름과 코드에 대해 접두어 변경을 실행하게 됩니다.

```
Replace prefix from `npbc` to `foo`. Are you sure? [Y/n]
```

접두어는 다음 규칙을 따릅니다.

1. 입력시 영소문자, 숫자, 대시(-), 밑줄(_)만 사용할 수 있습니다.
2. 접두어 첫글자는 영소문자만 가능합니다.
3. 대시나 밑줄은 두번이상 연속으로 사용해서는 안됩니다.
4. 접두어는 대시나 밑줄로 끝나서는 안됩니다.
5. 새로운 접두어는 'nbpc'나 'cpbn'이라는 단어를 포함하면 안됩니다.
6. 접두의 최대 길이는 25자입니다.

접두어는 코드나 파일 이름 치환시 다음처럼 사용됟니다.

1. 클래스 이름과 상수를 위해 영대문자와 밑줄을 사용합니다. 대시는 밑줄로 치환됩니다. (예: ABC_DEF)
2. 함수나 일반적인 문자열에서는 영소문자와 밑줄을 사용합니다. 대시는 밑줄로 치환됩니다. (예: abc_def)
3. 접두어만 정확하게 홑따옴표나 쌍따옴표로 감싼 문자열에 대해서는 영소문자와 대시를 사용합니다. 밑줄은 대시로 치환됩니다. (예 'abc-def')

차후 별도로 실행할 때는 `wp-content/plugin/boilerplate/bin/prefix-change.php`
스크립트를 CLI에서 실행하면 됩니다.

```
$ cd boilerplate
$ php bin/prefix-changer.php myplugin               # 접두 npbc를 myplugin 으로 변경.
$ php bin/prefix-changer.php my-new-prefix myplugin # 접두 myplugin를 my-new-prefix 으로 변경.
```

### 버전 일괄 변경

플러그인 메인 헤더 파일의 'version' 태그 정보를 기준으로 정의된 버전 문자열을 동기화할 수 있습니다. 예를 들어, 메인의 헤더 파일이 아래처럼 되어 있다면,

```php
<?php
/*
 * Plugin Name: Sample
 * Description: Sample plugin.
 * Version:     1.1.0
 */

// 이하 생략
```

플러그인의 버전은 1.1.0입니다. 그런데 이에 맞춰 `package.json`, `composer.json`이나 메인 파일 자체에 있는 상수 선언도 이에 맞춰 같은 문자열을 가져야 합니다. 이
때, `bin/sync-version.php` 스크립트를 사용할 수 있습니다.

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

## 플러그인 예제

### 보일러플레이트 설치

'FOO'라는 플러그인을 예제로 생성해 봅니다.

`wp-content/plugin` 디렉토리에서 다음을 실행합니다.

```
$ composer create-project naran/boilerplate foo
Creating a "naran/boilerplate" project at "./foo"

(...)

Please enter your new prefix (Enter 'exit' to skip): foo
Replace prefix from `nbpc` to `foo`. Are you sure? [Y/n] y

(...)
```

모든 파일의 접두가 패치됩니다.

### 루트의 파일 내용 수정

루트에는 플러그인에 대한 여러 설정 파일이 담겨 있습니다. 플러그인에 대한 여러 정보가 기록되는 부분이므로, 설치 직후 적절히 변경해 주어야 합니다.

#### README.me 파일

README.md 파일은 보일러플레이트 기본 그대로 되어 있습니다. 이 내용을 수정하세요.

#### index.php

index.php 에는 플러그인 헤더가 기록되어 있습니다. 몇몇 내용은 수정되었지만, 고쳐야 할 부분이 남아있습니다.

* Plugin Name
* Plugin URI
* Description
* Version
* Author
* Author URI

그리고 `const FOO_PRIORITY = 100;`에서 숫자를 적당히 조절해세요. 이 숫자는 모듈에서 사용하는 액션과 필터의 기본 우선순위에 사용됩니다.

#### composer.json

composer.json 또한 기본 그대로 되어 있습니다. 몇몇 사항을 수정하세요.

* name
* description
* authors

#### package.json

노드를 사용하는 경우 이 파일을 사용할 수 있습니다. 몇몇 사항을 수정하세요.

* name
* description

### 마무리

파일의 내용을 다 수정하면, index.php 의 플러그인 헤더에 정의된 버전 번호에 따라 모두 업데이트하세요.

```
$ composer version
```

### 모듈 설정

`foo/includes/class-foo-main.php` 파일에 `Foo_Main` 클래스가 정의되어 있습니다. 이 클래스가 플러그인의 핵심인 메인 파일입니다.

클래스에 정의된 `get_early_modules()` 메소드로 이동합니다. 이 메소드는 연관 배열을 리턴하는데, 이 배열의 키는 모듈의 식별자이고, 값은 모듈의 인스턴스나 클래스, 혹은 익명 함수일 수 있습니다.

#### 'bar' 모듈 생성

시험삼아 bar 모듈을 생성해 봅니다.

`foo/includes/modules/class-foo-bar.php` 파일을 생성합니다. 아래처럼 파일을 작성합니다.
효과를 명확히 확인하기 위해 wp_die() 함수를 사용하니, 운영중인 사이트가 아닌 곳에서 진행하세요.

```php
<?php
/**
 * FOO: Bar module
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'FOO_Bar' ) ) {
  class FOO_Bar extends FOO_Main_Base {
    use FOO_Hook_Impl;
    
    public function __construct() {
      $this->add_action( 'init', 'init_callback' );
    }
    
    public function init_callback() {
      wp_die( 'Intentionally stopped.' );
    }
  }
}
```

#### 'bar' 모듈의 등록
아직 bar 모듈은 동작하지 않습니다. 동작하려면 메인 클래스에 해당 모듈을 등록해야 합니다.
FOO_Main 클래스의 get_early_modules() 메소드에 모듈을 등록합니다.

```php
protected function get_early_modules(): array {
  return [
    /* 생략 */
    'bar' => FOO_Bar::class,
  ];
}
```

또한 Foo_Main 클래스의 주석에 bar 모듈 접근에 대한 힌트도 추가해 줍니다.
아래처럼 `@property-read Foo_Bar $bar` 라인을 추가합니다.

```php
/**
 * Class FOO_Main
 *
 * @property-read FOO_Admins    $admins
 * @property-read FOO_Registers $registers
 * @property-read Foo_Bar       $bar
 */
final class FOO_Main extends FOO_Main_Base
```

그리고 오토로딩 설정을 갱신합니다.

```
@composer dump-autoload
```

플러그인을 활성화했다면, 사이트가 wp_die() 함수 때문에 'Intentionally stopped.'라는 메세지를 출력하며
동작을 멈출 것입니다.

#### 동적 모듈로 전환

bar 모듈을 클래스 이름으로 전달하면 언제나 모듈이 활성화 됩니다. 그러나 모듈을 필요한 경우에만 
동작시키고 싶은 경우가 있을 것입니다.

이 때 아래처럼 `Foo_Main::get_early_modules()` 메소드의 내용을 수정합니다.

```php
protected function get_early_modules(): array {
  return [
    /* 생략 */
    'bar' => function () { return new FOO_Bar(); },
  ];
}
```

클래스 이름의 문자열에서 Foo_Bar() 의 인스턴스를 생성하는 익명 함수로 변경되었습니다.
이제 사이트가 원래대로 동작할 것입니다. 즉 이제 bar 모듈을 명시적으로 부르기 전까지는 모듈은 생성되지 않습니다.

이제 bar 모듈을 불러 보도록 하겠습니다. GET 파라미터에 임의의 값을 보내면 동작시키도록 해 봅니다.
index.php 에서, `foo();` 밑에 아래 코드를 추가합니다.

```php
if ( isset( $_GET['foo'] ) && 'bar' === $_GET['foo'] ) {
    foo()->bar;
}
```

사이트는 정상적으로 동작하지만 URL 주소의 GET 파라미터에 'foo=bar'를 추가하면
다시 사이트는 'Intentionally stopped.'라는 메세지를 출력하며 동작을 멈출 것입니다.

#### 우선 모듈(early module)과 후속 모듈(late module)의 차이
우선 모듈, `get_early_modules()` 메소드로 등록하는 모듈의 등록 방식과
후속 모듈, `get_late_modules()` 메소드로 등록하는 모듈의 등록 방식은 동일합니다. 다만 후속 모듈은
`init` 액션의 좀 더 낮은 우선순위의 콜백에 불려진다는 점이 다릅니다.

자동으로 시작하는 모듈이나, 특정 기능이 코어에 등록된 이후에 정상적인 동작이 보장되는 모듈일 경우
`get_late_modules()` 메소드에 등록하는 것이 좋습니다.


## 보일러플레이트를 테마로 사용하기
몇 가지 수정을 통해 간편하게 테마의 기본 코드로 전환시킬 수 있습니다.

### Style.css 파일 생성
테마는 테마의 최상위 디렉토리에 `style.css` 파일을 반드시 위치시켜야 합니다.
아래 예시를 참고하여 `style.css` 파일을 생성합니다.

```css
/*
Theme Name:        Naran Boilerplate Code
Theme URI:         https://github.com/chwnam/naran-boilerplate-code
Description:       Naran boilerplate code for WordPress plugins/themes.
Version:           1.3.4
Requires at least: 5.1.0
Requires PHP:      7.0
Tested up to:      5.4
Author:            changwoo
Author URI:        https://blog.changwoo.pe.kr/
License:           GPL v2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html
Text Domain:       npbc
CPBN Version:      1.3.4
*/
```

## (선택) Screenshot.png 파일 추가
이미지 기본 사이즈는 1200px * 900px 입니다. 이 이미지는 테마 목록에서 사용됩니다.

## Functions.php 파일 생성
테마 안에서 PHP 코드의 진입점은 `functions.php`입니다. 그러므로 functions.php 파일을 테마 최상이 디렉토리에 만들고,
`index.php`에 있던 내용을 잘라 붙입니다.

그리고 중요한 마무리를 합니다. `nbpc()` 함수를 부르기 전에 `{PREFIX}_THEME = true;` 코드를 삽입합니다.
예시로 아래처럼 될 겁니다.

```php
<?php
/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

const NBPC_MAIN_FILE = __FILE__;
const NBPC_VERSION   = '1.3.4';
const NBPC_PRIORITY  = 100;
const NBPC_THEME     = true;

nbpc();
```

플러그인에 있던 헤더 주석은 삭제합니다. 해당 내용은 이미 테마의 style.css로 옮겼기 때문입니다.


## Index.php, header.php, footer.php 생성
이제 index.php는 테마의 템플릿으로 사용됩니다.
테마의 템플릿으로 변경하고, header.php, footer.php 파일을 생성합니다.


## composer.json 수정
`composer version` 명령의 수정이 필요합니다.
`composer > scripts > version`의 명령어를 아래처럼 변경합니다.

```
"version": "@php bin/sync-version.php style.css",
```


## {PREFIX}_Register_Theme_Support 사용
{PREFIX}_Registers 클래스 생성자에 사용된 assign_module() 메소드 호출의 인자에서,

```
// 'theme_support' =>{PREFIX}_Register_Theme_Support::class, // Only for themes.
```

부분의 주석을 해제합니다.


## 프론트 모듈 매핑
`{PREFIX}_Register_Theme_Support::map_front_modules()` 메소드에서 프론트 모듈을 매핑합니다.
매핑된 모듈은 `{prefix}_get_front_module()` 함수에서 얻을 수 있습니다.
