<?php

use PhpParser\Lexer\Emulative;
use PhpParser\Node;
use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;

if ( ! class_exists( 'NBPC_CLI_Token_Extract' ) ) {
	class NBPC_CLI_Token_Extract {
		private Parser $parser;

		/** @var Node[]|null $nodes */
		private array|null $nodes;

		private NBPC_CLI_Node_Visitor $visitor;

		private NodeTraverser $traverser;

		public function __construct( NBPC_CLI_Node_Visitor $visitor ) {
			$emulative = new Emulative(
				[
					'usedAttributes' => [
						'comments',
						'startFilePos',
						'endFilePos',
					],
				]
			);

			$this->parser    = ( new ParserFactory() )->create( ParserFactory::PREFER_PHP7, $emulative );
			$this->traverser = new NodeTraverser();
			$this->visitor   = $visitor;

			$this->traverser->addVisitor( $this->visitor );
		}

		/**
		 * Extract token information
		 *
		 * @param string $content   Input code.
		 * @param string $dump_path Node dump path. Defaults to empty string, or do dumping.
		 *
		 * @return NBPC_CLI_Token[]
		 */
		public function extract( string $content, string $dump_path = '' ): array {
			$this->nodes = $this->parser->parse( $content );

			if ( $dump_path ) {
				$this->dump( $dump_path );
			}

			$this->traverser->traverse( $this->nodes );

			return $this->visitor->get_tokens();
		}

		private function dump( string $file_name ): void {
			file_put_contents(
				$file_name,
				( new NodeDumper(
					[
						'dumpComments'  => true,
						'dumpPositions' => true,
					]
				) )->dump( $this->nodes )
			);
		}
	}
}
