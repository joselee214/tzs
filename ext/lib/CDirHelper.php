<?php
/**
 * 目录操作类目
 * Author: taoliujun
 * Date: 2012-04-10 15:03
 */

Class CDirHelper
{
	/**
	 * 创建多级目录
	 * @static
	 * @param string $dir
	 * @param string $chmod
	 * @return bool
	 */
	public static function mkdirs( $dir , $chmod = 0766 )
	{
		$bool = self::_mkdirs( dirname($dir) , $chmod );
		return $bool;
	}

	private static function _mkdirs( $dir , $chmod )
	{
		if ( !is_dir($dir) )
		{
			if( !self::_mkdirs( dirname($dir) , $chmod ) )
			{
				return false;
			}
			if( !mkdir( $dir , $chmod ) )
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * 删除目录
	 * @static
	 * @param $dir
	 * @return boolen
	 */
	public static function rmdirs($dir)
	{
		$d = dir($dir);
		while ( false !== ( $child = $d->read() ) )
		{
			if($child != '.' && $child != '..')
			{
				if (is_dir($dir.'/'.$child) )
				{
					self::rmdirs( $dir.'/'.$child );
				}
				else
				{
					unlink($dir.'/'.$child);
				}
			}
		}
		$d->close();
		rmdir($dir);
	}


}