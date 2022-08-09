<?php

use PhpParser\Node;
use PhpParser\Node\Const_ as NodeConst;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Const_ as StmtConst;
use PHpParser\Node\Scalar\String_ as ScalarString;
use PhpParser\NodeVisitorAbstract;

if ( ! class_exists( 'NBPC_CLI_Node_Visitor_Const' ) ) {
	class NBPC_CLI_Node_Visitor_Const extends NodeVisitorAbstract implements NBPC_CLI_Node_Visitor {
		private array $tokens = [];

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
			if (
				$node instanceof StmtConst &&
				! empty( $node->consts ) &&
				$node->consts[0] instanceof NodeConst &&
				$node->consts[0]->name instanceof Identifier &&
				$node->consts[0]->value instanceof ScalarString
			) {
				$name = $node->consts[0]->name;

				/** @var ScalarString $value */
				$value = $node->consts[0]->value;

				$this->tokens[] = new NBPC_CLI_Token_Const(
					new NBPC_CLI_Token(
						NBPC_CLI_Token::TYPE_IDENTIFIER,
						$name->toString(),
						$name->getStartFilePos(),
						$name->getEndFilePos() + 1
					),
					new NBPC_CLI_Token(
						NBPC_CLI_TOken::TYPE_STRING,
						$value->value,
						$value->getStartFilePos() + 1,
						$value->getEndFilePos()
					)
				);
			} elseif (
				$node instanceof FuncCall &&
				2 === count( $node->args ) &&
				$node->args[0]->value instanceof ScalarString &&
				$node->args[1]->value instanceof ScalarString
			) {
				/** @var ScalarString $name */
				$name = $node->args[0]->value;

				/** @var ScalarString $value */
				$value = $node->args[1]->value;

				$this->tokens[] = new NBPC_CLI_Token_Const(
					new NBPC_CLI_Token(
						NBPC_CLI_Token::TYPE_IDENTIFIER,
						$name->value,
						$name->getStartFilePos() + 1,
						$name->getEndFilePos()
					),
					new NBPC_CLI_Token(
						NBPC_CLI_TOken::TYPE_STRING,
						$value->value,
						$value->getStartFilePos() + 1,
						$value->getStartFilePos()
					)
				);
			}
		}
	}
}