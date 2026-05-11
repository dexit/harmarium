<?php
/**
 * Pure-PHP QR code SVG generator.
 * Byte mode, ECC Level M, versions 1–7 (up to 122 data bytes).
 * Returns a self-contained <svg> string.
 */

defined( 'ABSPATH' ) || exit;

class Harmarium_QR {

	/* ── Galois Field GF(256) tables ── */
	private static array $EXP = [];
	private static array $LOG = [];

	private static function gf_init(): void {
		if ( self::$EXP ) return;
		self::$EXP = array_fill( 0, 512, 0 );
		self::$LOG  = array_fill( 0, 256, 0 );
		$x = 1;
		for ( $i = 0; $i < 255; $i++ ) {
			self::$EXP[ $i ] = $x;
			self::$LOG[ $x ]  = $i;
			$x <<= 1;
			if ( $x & 0x100 ) $x ^= 0x11d;
		}
		for ( $i = 255; $i < 512; $i++ ) {
			self::$EXP[ $i ] = self::$EXP[ $i - 255 ];
		}
	}

	private static function gf_mul( int $a, int $b ): int {
		if ( $a === 0 || $b === 0 ) return 0;
		return self::$EXP[ ( self::$LOG[ $a ] + self::$LOG[ $b ] ) % 255 ];
	}

	private static function gf_poly_mul( array $p, array $q ): array {
		$r = array_fill( 0, count( $p ) + count( $q ) - 1, 0 );
		foreach ( $p as $i => $pi ) {
			foreach ( $q as $j => $qj ) {
				$r[ $i + $j ] ^= self::gf_mul( $pi, $qj );
			}
		}
		return $r;
	}

	private static function gf_poly_div( array $msg, array $gen ): array {
		$out = $msg;
		$glen = count( $gen );
		$mlen = count( $msg );
		for ( $i = 0; $i < $mlen; $i++ ) {
			$coef = $out[ $i ];
			if ( $coef === 0 ) continue;
			for ( $j = 1; $j < $glen; $j++ ) {
				$out[ $i + $j ] ^= self::gf_mul( $gen[ $j ], $coef );
			}
		}
		return array_slice( $out, $mlen );
	}

	private static function rs_generator( int $nsym ): array {
		$g = [1];
		for ( $i = 0; $i < $nsym; $i++ ) {
			$g = self::gf_poly_mul( $g, [1, self::$EXP[ $i ]] );
		}
		return $g;
	}

	private static function rs_encode( array $data, int $nsym ): array {
		$gen = self::rs_generator( $nsym );
		$msg = array_merge( $data, array_fill( 0, $nsym, 0 ) );
		$rem = self::gf_poly_div( $msg, $gen );
		return array_merge( $data, $rem );
	}

	/* ── Version / capacity tables (ECC Level M) ── */
	// [version => [total_codewords, data_codewords, ec_codewords, groups, blocks_per_group, data_per_block]]
	private static array $VER = [
		1 => [26,  16, 10, 1, 1, 16],
		2 => [44,  28, 16, 1, 1, 28],
		3 => [70,  44, 26, 1, 1, 44],
		4 => [100, 64, 36, 1, 1, 64],  // simplified single-block for v1-4
		5 => [134, 86, 48, 2, 2, 43],
		6 => [172, 108,64, 2, 2, 54],
		7 => [196, 124,72, 2, 2, 62],
	];

	private static function pick_version( int $len ): int {
		// data bytes for Byte mode: 2 (mode+cc) + len; roughly
		$needed = $len;
		foreach ( self::$VER as $v => $t ) {
			if ( $needed <= $t[1] - 3 ) return $v; // -3 for mode indicator + char count
		}
		throw new \InvalidArgumentException( 'Data too long for QR v7 (max ~120 bytes).' );
	}

	/* ── BCH for format information ── */
	private static function bch_format( int $data ): int {
		$g = 0x537;
		$d = $data << 10;
		for ( $i = 4; $i >= 0; $i-- ) {
			if ( $d & ( 1 << ( $i + 10 ) ) ) $d ^= $g << $i;
		}
		return ( ( $data << 10 ) | $d ) ^ 0x5412;
	}

	/* ── Matrix helpers ── */
	private static function module_size( int $version ): int { return 21 + ( $version - 1 ) * 4; }

	private static function blank( int $n ): array { return array_fill( 0, $n, array_fill( 0, $n, -1 ) ); }

	private static function set_finder( array &$m, int $r, int $c ): void {
		for ( $i = 0; $i < 7; $i++ ) {
			for ( $j = 0; $j < 7; $j++ ) {
				$border = ( $i === 0 || $i === 6 || $j === 0 || $j === 6 );
				$inner  = ( $i >= 2 && $i <= 4 && $j >= 2 && $j <= 4 );
				$m[ $r + $i ][ $c + $j ] = ( $border || $inner ) ? 1 : 0;
			}
		}
		// separator
		for ( $i = -1; $i <= 7; $i++ ) {
			if ( $r + $i >= 0 && $r + $i < count( $m ) ) {
				if ( $c - 1 >= 0 ) $m[ $r + $i ][ $c - 1 ] = 0;
				if ( $c + 7 < count( $m ) ) $m[ $r + $i ][ $c + 7 ] = 0;
			}
			if ( $c + $i >= 0 && $c + $i < count( $m ) ) {
				if ( $r - 1 >= 0 ) $m[ $r - 1 ][ $c + $i ] = 0;
				if ( $r + 7 < count( $m ) ) $m[ $r + 7 ][ $c + $i ] = 0;
			}
		}
	}

	private static function set_alignment( array &$m, int $r, int $c ): void {
		for ( $i = -2; $i <= 2; $i++ ) {
			for ( $j = -2; $j <= 2; $j++ ) {
				$v = ( abs( $i ) === 2 || abs( $j ) === 2 || ( $i === 0 && $j === 0 ) ) ? 1 : 0;
				if ( $m[ $r + $i ][ $c + $j ] === -1 ) $m[ $r + $i ][ $c + $j ] = $v;
			}
		}
	}

	// Alignment pattern centres for versions 1-7
	private static array $ALIGN = [
		1 => [],
		2 => [6, 18], 3 => [6, 22], 4 => [6, 26],
		5 => [6, 30], 6 => [6, 34], 7 => [6, 22, 38],
	];

	private static function place_alignments( array &$m, int $v ): void {
		$pos = self::$ALIGN[ $v ];
		if ( ! $pos ) return;
		foreach ( $pos as $r ) {
			foreach ( $pos as $c ) {
				if ( $m[ $r ][ $c ] !== -1 ) continue;
				self::set_alignment( $m, $r, $c );
			}
		}
	}

	private static function set_timing( array &$m, int $n ): void {
		for ( $i = 8; $i < $n - 8; $i++ ) {
			$v = ( $i % 2 === 0 ) ? 1 : 0;
			if ( $m[6][$i] === -1 ) $m[6][$i] = $v;
			if ( $m[$i][6] === -1 ) $m[$i][6] = $v;
		}
	}

	private static function set_dark_module( array &$m, int $v ): void {
		$m[ 4 * $v + 9 ][8] = 1;
	}

	private static function reserve_format( array &$m, int $n ): void {
		// Format info areas (set to 0 as placeholder)
		for ( $i = 0; $i <= 8; $i++ ) {
			if ( $m[8][$i] === -1 ) $m[8][$i] = 0;
			if ( $m[$i][8] === -1 ) $m[$i][8] = 0;
		}
		for ( $i = $n - 8; $i < $n; $i++ ) {
			if ( $m[8][$i] === -1 ) $m[8][$i] = 0;
			if ( $m[$i][8] === -1 ) $m[$i][8] = 0;
		}
	}

	private static function write_format( array &$m, int $n, int $mask ): void {
		// ECC Level M = 0b00; mask pattern
		$format = self::bch_format( ( 0b00 << 3 ) | $mask );
		$bits = [];
		for ( $i = 0; $i < 15; $i++ ) {
			$bits[] = ( $format >> ( 14 - $i ) ) & 1;
		}
		$seq1 = [0,1,2,3,4,5,7,8];
		$seq2 = array_reverse( [0,1,2,3,4,5,7] );
		foreach ( $seq1 as $idx => $col ) { $m[8][$col] = $bits[$idx]; }
		$m[8][$n - 8] = $bits[8];
		foreach ( $seq2 as $idx => $row ) { $m[$row][8] = $bits[ $idx + 8 ]; }
		// bottom-left copy
		for ( $i = 0; $i < 7; $i++ ) { $m[ $n - 1 - $i ][8] = $bits[ $i ]; }
		for ( $i = 0; $i < 8; $i++ ) { $m[8][ $n - 8 + $i ] = $bits[ 7 + $i ]; }
	}

	private static function place_data( array &$m, array $data, int $mask, int $n ): void {
		$bits = [];
		foreach ( $data as $byte ) {
			for ( $i = 7; $i >= 0; $i-- ) $bits[] = ( $byte >> $i ) & 1;
		}
		$bi = 0; $up = true;
		for ( $col = $n - 1; $col >= 1; $col -= 2 ) {
			if ( $col === 6 ) $col = 5; // skip timing column
			for ( $i = 0; $i < $n; $i++ ) {
				$row = $up ? ( $n - 1 - $i ) : $i;
				foreach ( [0, -1] as $dc ) {
					$c = $col + $dc;
					if ( $m[$row][$c] !== -1 ) continue;
					$bit = ( $bi < count( $bits ) ) ? $bits[ $bi++ ] : 0;
					$m[$row][$c] = $bit ^ self::mask_bit( $mask, $row, $c );
				}
			}
			$up = ! $up;
		}
	}

	private static function mask_bit( int $mask, int $r, int $c ): int {
		return match ( $mask ) {
			0 => ( ( $r + $c ) % 2 === 0 ) ? 1 : 0,
			1 => ( $r % 2 === 0 ) ? 1 : 0,
			2 => ( $c % 3 === 0 ) ? 1 : 0,
			3 => ( ( $r + $c ) % 3 === 0 ) ? 1 : 0,
			4 => ( ( intdiv( $r, 2 ) + intdiv( $c, 3 ) ) % 2 === 0 ) ? 1 : 0,
			5 => ( ( $r * $c % 2 ) + ( $r * $c % 3 ) === 0 ) ? 1 : 0,
			6 => ( ( ( $r * $c % 2 ) + ( $r * $c % 3 ) ) % 2 === 0 ) ? 1 : 0,
			7 => ( ( ( $r + $c ) % 2 + ( $r * $c % 3 ) ) % 2 === 0 ) ? 1 : 0,
			default => 0,
		};
	}

	private static function penalty( array $m ): int {
		$n = count( $m ); $p = 0;
		// Rule 1: runs of 5+
		for ( $r = 0; $r < $n; $r++ ) {
			$run = 1;
			for ( $c = 1; $c < $n; $c++ ) {
				if ( $m[$r][$c] === $m[$r][$c-1] ) { $run++; if ( $run === 5 ) $p += 3; elseif ( $run > 5 ) $p++; } else $run = 1;
			}
			$run = 1;
			for ( $c = 1; $c < $n; $c++ ) {
				if ( $m[$c][$r] === $m[$c-1][$r] ) { $run++; if ( $run === 5 ) $p += 3; elseif ( $run > 5 ) $p++; } else $run = 1;
			}
		}
		return $p;
	}

	/* ── Public API ── */
	public static function svg( string $text, int $px = 4, string $fg = '#000', string $bg = '#fff' ): string {
		self::gf_init();

		$data = array_values( unpack( 'C*', $text ) );
		$len  = count( $data );
		$v    = self::pick_version( $len );
		$info = self::$VER[ $v ];
		$n    = self::module_size( $v );

		// Build data codewords
		$bits = [];
		// Mode indicator: Byte = 0100
		foreach ( [0,1,0,0] as $b ) $bits[] = $b;
		// Character count (8 bits for version 1-9)
		for ( $i = 7; $i >= 0; $i-- ) $bits[] = ( $len >> $i ) & 1;
		// Data bytes
		foreach ( $data as $byte ) {
			for ( $i = 7; $i >= 0; $i-- ) $bits[] = ( $byte >> $i ) & 1;
		}
		// Terminator
		for ( $i = 0; $i < 4 && count( $bits ) < $info[1] * 8; $i++ ) $bits[] = 0;
		// Pad to byte boundary
		while ( count( $bits ) % 8 !== 0 ) $bits[] = 0;
		// Pad codewords
		$pads = [0xec, 0x11];
		$pi = 0;
		while ( count( $bits ) < $info[1] * 8 ) {
			foreach ( [7,6,5,4,3,2,1,0] as $b ) $bits[] = ( $pads[$pi] >> $b ) & 1;
			$pi = 1 - $pi;
		}

		// Pack bits to bytes
		$cw = [];
		for ( $i = 0; $i < count( $bits ); $i += 8 ) {
			$byte = 0;
			for ( $j = 0; $j < 8; $j++ ) $byte = ( $byte << 1 ) | ( $bits[ $i + $j ] ?? 0 );
			$cw[] = $byte;
		}

		// RS encode (simplified single-block for v1-4, split for v5-7)
		if ( $v <= 4 ) {
			$encoded = self::rs_encode( $cw, $info[2] );
		} else {
			// Two groups, 2 blocks each
			$bpg = $info[4];
			$dpb = $info[5];
			$ecc = $info[2] / 4; // ec per block
			$blocks = [];
			$off = 0;
			for ( $b = 0; $b < 4; $b++ ) {
				$slice = array_slice( $cw, $off, $dpb );
				$blocks[] = self::rs_encode( $slice, (int) $ecc );
				$off += $dpb;
			}
			// Interleave data then EC
			$encoded = [];
			for ( $i = 0; $i < $dpb; $i++ ) foreach ( $blocks as $bl ) if ( isset( $bl[$i] ) ) $encoded[] = $bl[$i];
			for ( $i = $dpb; $i < count( $blocks[0] ); $i++ ) foreach ( $blocks as $bl ) if ( isset( $bl[$i] ) ) $encoded[] = $bl[$i];
		}

		// Choose best mask (0-3 quick scan to save CPU)
		$best_m = 0; $best_p = PHP_INT_MAX;
		for ( $mask = 0; $mask < 4; $mask++ ) {
			$m = self::blank( $n );
			self::set_finder( $m, 0, 0 );
			self::set_finder( $m, 0, $n - 7 );
			self::set_finder( $m, $n - 7, 0 );
			self::place_alignments( $m, $v );
			self::set_timing( $m, $n );
			self::set_dark_module( $m, $v );
			self::reserve_format( $m, $n );
			self::place_data( $m, $encoded, $mask, $n );
			self::write_format( $m, $n, $mask );
			$p = self::penalty( $m );
			if ( $p < $best_p ) { $best_p = $p; $best_m = $mask; }
		}

		// Final matrix
		$m = self::blank( $n );
		self::set_finder( $m, 0, 0 );
		self::set_finder( $m, 0, $n - 7 );
		self::set_finder( $m, $n - 7, 0 );
		self::place_alignments( $m, $v );
		self::set_timing( $m, $n );
		self::set_dark_module( $m, $v );
		self::reserve_format( $m, $n );
		self::place_data( $m, $encoded, $best_m, $n );
		self::write_format( $m, $n, $best_m );

		// Render SVG
		$quiet  = 4;
		$total  = ( $n + $quiet * 2 ) * $px;
		$rects  = '';
		for ( $r = 0; $r < $n; $r++ ) {
			for ( $c = 0; $c < $n; $c++ ) {
				if ( $m[$r][$c] === 1 ) {
					$x = ( $c + $quiet ) * $px;
					$y = ( $r + $quiet ) * $px;
					$rects .= "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$px}\" height=\"{$px}\"/>";
				}
			}
		}
		$fg_esc = esc_attr( $fg );
		$bg_esc = esc_attr( $bg );
		return "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 {$total} {$total}\" width=\"{$total}\" height=\"{$total}\">"
			. "<rect width=\"{$total}\" height=\"{$total}\" fill=\"{$bg_esc}\"/>"
			. "<g fill=\"{$fg_esc}\">{$rects}</g>"
			. '</svg>';
	}

	public static function data_uri( string $text ): string {
		return 'data:image/svg+xml;base64,' . base64_encode( self::svg( $text ) );
	}
}
