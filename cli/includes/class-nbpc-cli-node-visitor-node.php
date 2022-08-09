<?php

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_ as ExprNew;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name as NodeName;
use PhpParser\Node\Scalar\String_ as ScalarString;
use PhpParser\Node\Stmt\Class_ as StmtClass;
use PhpParser\Node\Stmt\Interface_ as StmtInterface;
use PhpParser\Node\Stmt\Namespace_ as StmtNamespace;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\VarLikeIdentifier;
use PhpParser\NodeVisitorAbstract;

if ( ! class_exists( 'NBPC_CLI_Node_Visitor_Node' ) ) {
	/**
	 * Visit node and keep track of what we need
	 */
	class NBPC_CLI_Node_Visitor_Node extends NodeVisitorAbstract implements NBPC_CLI_Node_Visitor {
		private array $tokens = [];

		/** @var array{string: int} Key: function name, Value: index of textdomain part. */
		private array $l10n_functions;

		public function __construct() {
			$this->l10n_functions = [
				'__'         => 1,
				'_e'         => 1,
				'_ex'        => 2,
				'_n'         => 3,
				'_n_noop'    => 2,
				'_nx'        => 4,
				'_nx_noop'   => 3,
				'_x'         => 2,
				'esc_attr__' => 1,
				'esc_attr_e' => 1,
				'esc_attr_x' => 2,
				'esc_html__' => 1,
				'esc_html_e' => 1,
				'esc_html_x' => 2,
			];
		}

		public function beforeTraverse( array $nodes ) {
			$this->tokens = [];
		}

		public function leaveNode( Node $node ) {
			$this->extract_from_node( $node );
		}

		public function afterTraverse( array $nodes ) {
			ksort( $this->tokens );
		}

		/**
		 * @return NBPC_CLI_Token[]
		 */
		public function get_tokens(): array {
			return $this->tokens;
		}

		private function extract_from_node( Node $node ): void {
			if ( $node instanceof Identifier ) {
				// Extracting identifier: constants, classs, functions, methods, and properties in classes.
				if ( $node instanceof VarLikeIdentifier ) {
					// Property in classe.
					$this->add_token(
						NBPC_CLI_Token::TYPE_VARIABLE,
						$node->name,
						$node->getStartFilePos() + 1,
						$node->getEndFilePos() + 1
					);
				} else {
					$this->add_token(
						NBPC_CLI_Token::TYPE_IDENTIFIER,
						$node->name,
						$node->getStartFilePos(),
						$node->getEndFilePos() + 1
					);
				}
			} elseif ( $node instanceof Name ) {
				$this->add_token(
					NBPC_CLI_Token::TYPE_IDENTIFIER,
					$node->toString(),
					$node->getStartFilePos(),
					$node->getEndFilePos() + 1
				);
			} elseif ( $node instanceof StmtNamespace ) {
				// Extracting namespace declarations.
				$this->add_token(
					NBPC_CLI_Token::TYPE_NAMESPACE,
					$node->name->toString(),
					$node->name->getStartFilePos(),
					$node->name->getEndFilePos() + 1
				);
			} elseif ( $node instanceof UseUse ) {
				// Extracting namespace use decarations.
				$this->add_token(
					NBPC_CLI_Token::TYPE_NAMESPACE,
					$node->name,
					$node->name->getStartFilePos(),
					$node->name->getEndFilePos() + 1
				);
			} elseif ( $node instanceof StmtInterface ) {
				// Extracting extends from interfaces.
				foreach ( $node->extends as $extend ) {
					/** @var NodeName $extend */
					$this->add_token(
						NBPC_CLI_Token::TYPE_IDENTIFIER,
						$extend->toString(),
						$extend->getStartFilePos(),
						$extend->getEndFilePos() + 1
					);
				}
			} elseif ( $node instanceof StmtClass ) {
				// Extracting extends, implements from classes.
				if ( $node->extends ) {
					$this->add_token(
						NBPC_CLI_Token::TYPE_IDENTIFIER,
						$node->extends->toString(),
						$node->extends->getStartFilePos(),
						$node->extends->getEndFilePos() + 1
					);
				}
				foreach ( $node->implements as $implement ) {
					/** @var NodeName $implement */
					$this->add_token(
						NBPC_CLI_Token::TYPE_IDENTIFIER,
						$implement->toString(),
						$implement->getStartFilePos(),
						$implement->getEndFilePos() + 1
					);
				}
			} elseif ( $node instanceof Variable ) {
				// Extractiong variables.
				$this->add_token(
					NBPC_CLI_Token::TYPE_VARIABLE,
					$node->name,
					$node->getStartFilePos() + 1, // Varaibles have dollar signs.
					$node->getEndFilePos() + 1
				);
			} elseif ( $node instanceof TraitUse ) {
				// Extracting trait use.
				foreach ( $node->traits as $trait ) {
					$this->add_token(
						NBPC_CLI_Token::TYPE_IDENTIFIER,
						$trait->toString(),
						$trait->getStartFilePos(),
						$trait->getEndFilePos() + 1
					);
				}
			} elseif ( $node instanceof ExprNew && $node->class instanceof Name ) {
				// Extracting using keyword new for classes.
				// Class names via variable are ignored, like: $obj = new $class();
				$this->add_token(
					NBPC_CLI_Token::TYPE_IDENTIFIER,
					$node->class->toString(),
					$node->class->getStartFilePos(),
					$node->class->getEndFilePos() + 1
				);
			} elseif ( $node instanceof FuncCall && $node->name instanceof Name ) {
				// Extracting text domains.
				// L10N fuctions are called via identifier.
				// They don't be used like this: $var = function () {}; $var();
				$func_name = $node->name->toString();
				$position  = $this->l10n_functions[ $func_name ] ?? 0; // Every textdomain position is greater than 0.
				if ( $position && isset( $node->args[ $position ] ) ) {
					$textdomain = $node->args[ $position ]?->value;
					if ( $textdomain instanceof ScalarString ) {
						$this->add_token(
							NBPC_CLI_Token::TYPE_TEXTDOMAIN,
							$textdomain->value,
							$textdomain->getStartFilePos() + 1, // String is enclosed with quotation marks.
							$textdomain->getEndFilePos(),
							true // Overwrite.
						);
					}
				}
			} elseif ( $node instanceof ScalarString ) {
				// Extract plain strings.
				$this->add_token(
					NBPC_CLI_Token::TYPE_STRING,
					$node->value,
					$node->getStartFilePos() + 1, // String quotation.
					$node->getEndFilePos()
				);
			}

			// Extract comments.
			foreach ( $node->getComments() as $comment ) {
				$this->add_token(
					NBPC_CLI_Token::TYPE_COMMENT,
					$comment->getText(),
					$comment->getStartFilePos(),
					$comment->getEndFilePos() + 1
				);
			}
		}

		private function add_token(
			string $type,
			string $value,
			int $startPos,
			int $endPos,
			bool $overwrite = false
		): void {
			if ( $overwrite || ! isset( $this->tokens[ $startPos ] ) ) {
				$this->tokens[ $startPos ] = new NBPC_CLI_Token( $type, $value, $startPos, $endPos );
			}
		}
	}
}
