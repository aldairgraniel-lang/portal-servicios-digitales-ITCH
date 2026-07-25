<?php
/*******************************************************************************
* FPDF                                                                         *
* *
* Version: 1.86                                                                *
* Date:    2023-06-25                                                          *
* Author:  Olivier PLATNEY                                                     *
*******************************************************************************/

define('FPDF_VERSION','1.86');

class FPDF
{
    protected $page;               // current page number
    protected $n;                  // current object number
    protected $offsets;            // array of object offsets
    protected $buffer;             // buffer holding in-memory PDF
    protected $pages;              // array containing pages
    protected $state;              // current document state
    protected $compress;           // compression flag
    protected $k;                  // scale factor (number of points in user unit)
    protected $DefOrientation;     // default orientation
    protected $CurOrientation;     // current orientation
    protected $StdPageSizes;       // standard page sizes
    protected $DefPageSize;        // default page size
    protected $CurPageSize;        // current page size
    protected $CurDisplayMode;     // layout and zoom display mode
    protected $PageSizes;          // used for well-defined sizes of pages
    protected $wPt, $hPt;          // dimensions of current page in points
    protected $w, $h;              // dimensions of current page in user units
    protected $lMargin;            // left margin
    protected $tMargin;            // top margin
    protected $rMargin;            // right margin
    protected $bMargin;            // page break margin
    protected $cMargin;            // cell margin
    protected $x, $y;              // current position in user units
    protected $lasth;              // height of last printed cell
    protected $LineWidth;          // line width in user units
    protected $fontpath;           // path for standard fonts
    protected $CoreFonts;          // array of core font names
    protected $fonts;              // array of used fonts
    protected $FontFiles;          // array of font files
    protected $encodings;          // array of encodings
    protected $cmaps;              // array of ToUnicode CMaps
    protected $FontFamily;         // current font family
    protected $FontStyle;          // current font style
    protected $underline;          // underlining flag
    protected $CurrentFont;        // current font info
    protected $FontSizePt;         // current font size in points
    protected $FontSize;           // current font size in user units
    protected $ColorFlag;          // flag for color regularity
    protected $WithAlpha;          // flag for alpha channel
    protected $ws;                 // word spacing
    protected $images;             // array of used images
    protected $PageLinks;          // array of links in pages
    protected $links;              // array of internal links
    protected $AutoPageBreak;      // automatic page breaking
    protected $PageBreakTrigger;   // threshold used to trigger page breaks
    protected $InHeader;           // flag set when processing header
    protected $InFooter;           // flag set when processing footer
    protected $AliasNbPages;       // alias for total number of pages
    protected $ZoomMode;           // zoom display mode
    protected $LayoutMode;         // layout display mode
    protected $metadata;           // document metadata
    protected $PDFVersion;         // PDF version number

    public function __construct($orientation='P', $unit='mm', $size='A4')
    {
        $this->_docheck();
        $this->page = 0;
        $this->n = 2;
        $this->buffer = '';
        $this->pages = array();
        $this->PageSizes = array();
        $this->state = 0;
        $this->fonts = array();
        $this->FontFiles = array();
        $this->encodings = array();
        $this->cmaps = array();
        $this->images = array();
        $this->links = array();
        $this->InHeader = false;
        $this->InFooter = false;
        $this->lasth = 0;
        $this->FontFamily = '';
        $this->FontStyle = '';
        $this->FontSizePt = 12;
        $this->underline = false;
        $this->DrawColor = '0 G';
        $this->FillColor = '0 g';
        $this->TextColor = '0 g';
        $this->ColorFlag = false;
        $this->WithAlpha = false;
        $this->ws = 0;
        $this->fontpath = defined('FPDF_FONTPATH') ? FPDF_FONTPATH : __DIR__.'/font/';
        $this->CoreFonts = array('courier', 'helvetica', 'times', 'symbol', 'zapfdingbats');
        if($unit=='pt')
            $this->k = 1;
        elseif($unit=='mm')
            $this->k = 72/25.4;
        elseif($unit=='cm')
            $this->k = 72/2.54;
        elseif($unit=='in')
            $this->k = 72;
        else
            $this->Error('Incorrect unit: '.$unit);
        if(is_string($size))
        {
            $size = strtolower($size);
            if($size=='a3')
                $size = array(841.89,1190.55);
            elseif($size=='a4')
                $size = array(595.28,841.89);
            elseif($size=='a5')
                $size = array(420.94,595.28);
            elseif($size=='letter')
                $size = array(612,792);
            elseif($size=='legal')
                $size = array(612,1008);
            else
                $this->Error('Incorrect page size: '.$size);
            $this->DefPageSize = $size;
        }
        else
        {
            if($size[0]>$size[1])
                $this->DefPageSize = array($size[1]*$this->k, $size[0]*$this->k);
            else
                $this->DefPageSize = array($size[0]*$this->k, $size[1]*$this->k);
        }
        $orientation = strtolower($orientation);
        if($orientation=='p' || $orientation=='portrait')
        {
            $this->DefOrientation = 'P';
            $this->w = $this->DefPageSize[0];
            $this->h = $this->DefPageSize[1];
        }
        elseif($orientation=='l' || $orientation=='landscape')
        {
            $this->DefOrientation = 'L';
            $this->w = $this->DefPageSize[1];
            $this->h = $this->DefPageSize[0];
        }
        else
            $this->Error('Incorrect orientation: '.$orientation);
        $this->wPt = $this->w;
        $this->hPt = $this->h;
        $this->w /= $this->k;
        $this->h /= $this->k;
        $this->lMargin = 28.35/$this->k;
        $this->tMargin = 28.35/$this->k;
        $this->rMargin = 28.35/$this->k;
        $this->bMargin = 56.69/$this->k;
        $this->cMargin = $this->lMargin/10;
        $this->LineWidth = .567/$this->k;
        $this->SetDisplayMode('default');
        $this->SetAutoPageBreak(true, 2 * $this->bMargin);
        $this->PDFVersion = '1.3';
    }

    public function SetMargins($left, $top, $right=null)
    {
        $this->lMargin = $left;
        $this->tMargin = $top;
        if($right===null)
            $right = $left;
        $this->rMargin = $right;
    }

    public function SetLeftMargin($margin)
    {
        $this->lMargin = $margin;
        if($this->page>0 && $this->x<$margin)
            $this->x = $margin;
    }

    public function SetTopMargin($margin)
    {
        $this->tMargin = $margin;
    }

    public function SetRightMargin($margin)
    {
        $this->rMargin = $margin;
    }

    public function SetAutoPageBreak($auto, $margin=0)
    {
        $this->AutoPageBreak = $auto;
        $this->bMargin = $margin;
        $this->PageBreakTrigger = $this->h-$margin;
    }

    public function SetDisplayMode($zoom, $layout='default')
    {
        if($zoom=='fullpage' || $zoom=='fullwidth' || $zoom=='real' || $zoom=='default' || !is_string($zoom))
            $this->ZoomMode = $zoom;
        else
            $this->Error('Incorrect zoom display mode: '.$zoom);
        if($layout=='single' || $layout=='continuous' || $layout=='two' || $layout=='default')
            $this->LayoutMode = $layout;
        else
            $this->Error('Incorrect layout display mode: '.$layout);
    }

    public function SetCompression($compress)
    {
        if(function_exists('gzcompress'))
            $this->compress = $compress;
        else
            $this->compress = false;
    }

    public function SetTitle($title, $isUTF8=false)
    {
        $this->title = $isUTF8 ? $title : utf8_encode($title);
    }

    public function SetSubject($subject, $isUTF8=false)
    {
        $this->subject = $isUTF8 ? $subject : utf8_encode($subject);
    }

    public function SetAuthor($author, $isUTF8=false)
    {
        $this->author = $isUTF8 ? $author : utf8_encode($author);
    }

    public function SetKeywords($keywords, $isUTF8=false)
    {
        $this->keywords = $isUTF8 ? $keywords : utf8_encode($keywords);
    }

    public function SetCreator($creator, $isUTF8=false)
    {
        $this->creator = $isUTF8 ? $creator : utf8_encode($creator);
    }

    public function AliasNbPages($alias='{nb}')
    {
        $this->AliasNbPages = $alias;
    }

    public function Error($msg)
    {
        throw new Exception('FPDF error: '.$msg);
    }

    public function Close()
    {
        if($this->state==3)
            return;
        if($this->page==0)
            $this->AddPage();
        $this->InFooter = true;
        $this->Footer();
        $this->InFooter = false;
        $this->_endpage();
        $this->_enddoc();
    }

    public function AddPage($orientation='', $size='', $rotation=0)
    {
        if($this->state==3)
            $this->Error('The document is closed');
        $family = $this->FontFamily;
        $style = $this->FontStyle.($this->underline ? 'U' : '');
        $sizePt = $this->FontSizePt;
        $lw = $this->LineWidth;
        $dc = $this->DrawColor;
        $fc = $this->FillColor;
        $tc = $this->TextColor;
        $cf = $this->ColorFlag;
        if($this->page>0)
        {
            $this->InFooter = true;
            $this->Footer();
            $this->InFooter = false;
            $this->_endpage();
        }
        $this->_beginpage($orientation,$size,$rotation);
        $this->_out('2 J');
        $this->LineWidth = $lw;
        $this->_out(sprintf('%.2F w',$lw*$this->k));
        if($family)
            $this->SetFont($family,$style,$sizePt);
        $this->DrawColor = $dc;
        if($dc!='0 G')
            $this->_out($dc);
        $this->FillColor = $fc;
        if($fc!='0 g')
            $this->_out($fc);
        $this->TextColor = $tc;
        $this->ColorFlag = $cf;
        $this->InHeader = true;
        $this->Header();
        $this->InHeader = false;
        if($this->LineWidth!=$lw)
        {
            $this->LineWidth = $lw;
            $this->_out(sprintf('%.2F w',$lw*$this->k));
        }
        if($family)
            $this->SetFont($family,$style,$sizePt);
        if($this->DrawColor!=$dc)
        {
            $this->DrawColor = $dc;
            $this->_out($dc);
        }
        if($this->FillColor!=$fc)
        {
            $this->FillColor = $fc;
            $this->_out($fc);
        }
        if($this->TextColor!=$tc)
            $this->TextColor = $tc;
        $this->ColorFlag = $cf;
    }

    public function Header()
    {
    }

    public function Footer()
    {
    }

    public function PageNo()
    {
        return $this->page;
    }

    public function SetDrawColor($r, $g=null, $b=null)
    {
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->DrawColor = sprintf('%.3F G',$r/255);
        else
            $this->DrawColor = sprintf('%.3F %.3F %.3F RG',$r/255,$g/255,$b/255);
        if($this->page>0)
            $this->_out($this->DrawColor);
    }

    public function SetFillColor($r, $g=null, $b=null)
    {
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->FillColor = sprintf('%.3F g',$r/255);
        else
            $this->FillColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
        $this->ColorFlag = ($this->TextColor!=$this->FillColor);
        if($this->page>0)
            $this->_out($this->FillColor);
    }

    public function SetTextColor($r, $g=null, $b=null)
    {
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->TextColor = sprintf('%.3F g',$r/255);
        else
            $this->TextColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
        $this->ColorFlag = ($this->TextColor!=$this->FillColor);
    }

    public function GetStringWidth($s)
    {
        $s = (string)$s;
        $cw = &$this->CurrentFont['cw'];
        $w = 0;
        $l = strlen($s);
        for($i=0;$i<$l;$i++)
            $w += $cw[$s[$i]];
        return $w*$this->FontSize/1000;
    }

    public function SetLineWidth($width)
    {
        $this->LineWidth = $width;
        if($this->page>0)
            $this->_out(sprintf('%.2F w',$width*$this->k));
    }

    public function Line($x1, $y1, $x2, $y2)
    {
        $this->_out(sprintf('%.2F %.2F m %.2F %.2F l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
    }

    public function Rect($x, $y, $w, $h, $style='')
    {
        if($style=='F')
            $op = 'f';
        elseif($style=='FD' || $style=='DF')
            $op = 'B';
        else
            $op = 's';
        $this->_out(sprintf('%.2F %.2F %.2F %.2F re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$op));
    }

    public function AddFont($family, $style='', $file='')
    {
        $family = strtolower($family);
        if($file=='')
            $file = str_replace(' ','', $family).strtolower($style).'.php';
        $style = strtoupper($style);
        if($style=='IB')
            $style = 'BI';
        $fontkey = $family.$style;
        if(isset($this->fonts[$fontkey]))
            return;
        $info = $this->_loadfont($file);
        $info['i'] = count($this->fonts)+1;
        if(!empty($info['file']))
        {
            if($info['type']=='TrueType')
                $this->FontFiles[$info['file']] = array('length1'=>$info['originalsize']);
            else
                $this->FontFiles[$info['file']] = array('length1'=>$info['size1'],'length2'=>$info['size2']);
        }
        $this->fonts[$fontkey] = $info;
    }

    public function SetFont($family, $style='', $size=0)
    {
        $family = strtolower($family);
        if($family=='')
            $family = $this->FontFamily;
        if($family=='arial')
            $family = 'helvetica';
        elseif($family=='symbol' || $family=='zapfdingbats')
            $style = '';
        $style = strtoupper($style);
        if(strpos($style,'U')!==false)
        {
            $this->underline = true;
            $style = str_replace('U','',$style);
        }
        else
            $this->underline = false;
        if($style=='IB')
            $style = 'BI';
        if($size==0)
            $size = $this->FontSizePt;
        if($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size)
            return;
        $fontkey = $family.$style;
        if(!isset($this->fonts[$fontkey]))
        {
            if($family=='helvetica' || $family=='times' || $family=='courier' || $family=='symbol' || $family=='zapfdingbats')
                $this->AddFont($family,$style);
            else
                $this->Error('Undefined font: '.$family.' '.$style);
        }
        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size/$this->k;
        $this->CurrentFont = &$this->fonts[$fontkey];
        if($this->page>0)
            $this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
    }

    public function SetFontSize($size)
    {
        if($this->FontSizePt==$size)
            return;
        $this->FontSizePt = $size;
        $this->FontSize = $size/$this->k;
        if($this->page>0)
            $this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
    }

    public function AddLink()
    {
        $n = count($this->links)+1;
        $this->links[$n] = array(0, 0);
        return $n;
    }

    public function SetLink($link, $y=0, $page=-1)
    {
        if($page==-1)
            $page = $this->page;
        $this->links[$link] = array($page, $y);
    }

    public function Link($x, $y, $w, $h, $link)
    {
        $this->PageLinks[$this->page][] = array($x*$this->k, ($this->h-$y)*$this->k, $w*$this->k, -$h*$this->k, $link);
    }

    public function Text($x, $y, $txt)
    {
        $txt = (string)$txt;
        if($this->ColorFlag)
            $s = sprintf('q %s BT %.2F %.2F Td (%s) Tj ET Q',$this->TextColor,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
        else
            $s = sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
        if($this->underline && $txt!=='')
            $s .= ' '.$this->_underline($x,$y,$txt);
        $this->_out($s);
    }

    public function AcceptPageBreak()
    {
        return $this->AutoPageBreak;
    }

    public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $txt = (string)$txt;
        $k = $this->k;
        if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
        {
            $x = $this->x;
            $ws = $this->ws;
            if($ws>0)
            {
                $this->ws = 0;
                $this->_out('0 Tw');
            }
            $this->AddPage($this->CurOrientation,$this->CurPageSize);
            $this->x = $x;
            if($ws>0)
            {
                $this->ws = $ws;
                $this->_out(sprintf('%.3F Tw',$ws*$k));
            }
        }
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $s = '';
        if($fill || $border==1)
        {
            if($fill)
                $op = ($border==1) ? 'B' : 'f';
            else
                $op = 's';
            $s = sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
        }
        if(is_string($border))
        {
            $x = $this->x;
            $y = $this->y;
            if(strpos($border,'L')!==false) $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
            if(strpos($border,'T')!==false) $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
            if(strpos($border,'R')!==false) $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
            if(strpos($border,'B')!==false) $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
        }
        if($txt!=='')
        {
            if(!isset($this->CurrentFont))
                $this->Error('No font has been set');
            if($align=='R')
                $dx = $w-$this->cMargin-$this->GetStringWidth($txt);
            elseif($align=='C')
                $dx = ($w-$this->GetStringWidth($txt))/2;
            else
                $dx = $this->cMargin;
            if($this->ColorFlag)
                $s .= sprintf('q %s ',$this->TextColor);
            $s .= sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$this->_escape($txt));
            if($this->underline)
                $s .= ' '.$this->_underline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
            if($this->ColorFlag)
                $s .= ' Q';
            if($link)
                $this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$this->GetStringWidth($txt),$this->FontSize,$link);
        }
        if($s)
            $this->_out($s);
        $this->lasth = $h;
        if($ln>0)
        {
            $this->y += $h;
            if($ln==1)
                $this->x = $this->lMargin;
        }
        else
            $this->x += $w;
    }

    public function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
    {
        if(!isset($this->CurrentFont))
            $this->Error('No font has been set');
        $cw = &$this->CurrentFont['cw'];
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'', $txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n")
            $nb--;
        $b = 0;
        if($border)
        {
            if($border==1)
            {
                $border = 'LTRB';
                $b = 'LRT';
                $b2 = 'LR';
            }
            else
            {
                $b2 = '';
                if(strpos($border,'L')!==false) $b2 .= 'L';
                if(strpos($border,'R')!==false) $b2 .= 'R';
                $b = (strpos($border,'T')!==false) ? $b2.'T' : $b2;
            }
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $ns = 0;
        $nl = 1;
        while($i<$nb)
        {
            $c = $s[$i];
            if($c=="\n")
            {
                if($align=='J')
                {
                    $this->ws = 0;
                    $this->_out('0 Tw');
                }
                $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                if($border && $nl==2)
                    $b = $b2;
                continue;
            }
            if($c==' ')
            {
                $sep = $i;
                $ls = $l;
                $ns++;
            }
            $l += $cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                    if($align=='J')
                    {
                        $this->ws = 0;
                        $this->_out('0 Tw');
                    }
                    $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
                }
                else
                {
                    if($align=='J')
                    {
                        $this->ws = ($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
                        $this->_out(sprintf('%.3F Tw',$this->ws*$this->k));
                    }
                    $this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
                    $i = $sep+1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                if($border && $nl==2)
                    $b = $b2;
            }
            else
                $i++;
        }
        if($align=='J')
        {
            $this->ws = 0;
            $this->_out('0 Tw');
        }
        if($border && strpos($border,'B')!==false)
            $b .= 'B';
        $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
        $this->x = $this->lMargin;
    }

    public function Write($h, $txt, $link='')
    {
        if(!isset($this->CurrentFont))
            $this->Error('No font has been set');
        $cw = &$this->CurrentFont['cw'];
        $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'', $txt);
        $nb = strlen($s);
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i<$nb)
        {
            $c = $s[$i];
            if($c=="\n")
            {
                $this->Cell($this->x-$this->lMargin,$h,substr($s,$j,$i-$j),0,2,'L');
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $w = $this->w-$this->rMargin-$this->x;
                $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep = $i;
            $l += $cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($this->x>$this->lMargin)
                    {
                        $this->x = $this->lMargin;
                        $this->y += $h;
                        $w = $this->w-$this->rMargin-$this->x;
                        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
                        $i = $j;
                        $l = 0;
                        $nl++;
                        continue;
                    }
                    if($i==$j)
                        $i++;
                    $this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'L');
                }
                else
                {
                    $this->Cell($w,$h,substr($s,$j,$sep-$j),0,2,'L');
                    $i = $sep+1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $this->x = $this->lMargin;
                $this->y += $h;
                $w = $this->w-$this->rMargin-$this->x;
                $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
                $nl++;
            }
            else
                $i++;
        }
        if($i>$j)
            $this->Cell($this->GetStringWidth(substr($s,$j)),$h,substr($s,$j),0,0,'L',false,$link);
    }

    public function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='')
    {
        if($type=='')
        {
            $pos = strrpos($file,'.');
            if(!$pos)
                $this->Error('Image file has no extension and no type was specified: '.$file);
            $type = substr($file,$pos+1);
        }
        $type = strtolower($type);
        if($type=='jpeg')
            $type = 'jpg';
        $mtd = '_parse'.$type;
        if(!method_exists($this,$mtd))
            $this->Error('Unsupported image type: '.$type);
        $info = $this->$mtd($file);
        $info['i'] = count($this->images)+1;
        $this->images[$file] = $info;
        if($x===null) $x = $this->x;
        if($y===null) $y = $this->y;
        if($w==0 && $h==0) { $w = $info['w']/$this->k; $h = $info['h']/$this->k; }
        if($w==0) $w = $h*$info['w']/$info['h'];
        if($h==0) $h = $w*$info['h']/$info['w'];
        $this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q',$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']));
        if($link) $this->Link($x,$y,$w,$h,$link);
    }

    public function Ln($h=null)
    {
        $this->x = $this->lMargin;
        if($h===null)
            $this->y += $this->lasth;
        else
            $this->y += $h;
    }

    public function GetX() { return $this->x; }
    public function GetY() { return $this->y; }
    public function SetX($x) { if($x>=0) $this->x = $x; else $this->x = $this->w+$x; }
    public function SetY($y) { if($y>=0) { $this->x = $this->lMargin; $this->y = $y; } else $this->y = $this->h+$y; }
    public function SetXY($x, $y) { $this->SetY($y); $this->SetX($x); }

    public function Output($dest='', $name='', $isUTF8=false)
    {
        $this->Close();
        if(strlen($name)==0)
        {
            $name = 'doc.pdf';
            $dest = 'I';
        }
        if($dest=='I' || $dest=='D')
        {
            if(PHP_SAPI!='cli')
            {
                header('Content-Type: application/pdf');
                header('Content-Disposition: '.($dest=='D' ? 'attachment' : 'inline').'; filename="'.($isUTF8 ? $name : utf8_decode($name)).'"');
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
            }
        }
        echo $this->buffer;
    }

    protected function _docheck()
    {
        if(sprintf('%.1F',1.0)!='1.0') $this->Error('An locales dependency problem occurred: use setlocale(LC_NUMERIC, "C")');
    }

    protected function _loadfont($font)
    {
        if(strpos($font,'/')!==false || strpos($font,"\\")!==false) $this->Error('Incorrect font filename');
        include($this->fontpath.$font);
        if(!isset($name)) $this->Error('Could not load font description file');
        return get_defined_vars();
    }

    protected function _escape($s)
    {
        return str_replace(array('\\','(',')'), array('\\\\','\\(','\\)'), $s);
    }

    protected function _out($s) { $this->buffer .= $s."\n"; }
    protected function _beginpage($orientation, $size, $rotation) { $this->page++; $this->pages[$this->page] = ''; $this->state = 2; $this->x = $this->lMargin; $this->y = $this->tMargin; $this->FontFamily = ''; if($orientation=='') $orientation = $this->DefOrientation; else { $orientation = strtoupper($orientation[0]); if($orientation!=$this->DefOrientation) $this->PageSizes[$this->page] = $this->DefPageSize; } if($size=='') $size = $this->DefPageSize; }
    protected function _endpage() { $this->state = 1; }
    protected function _enddoc() { /* Implementation handles object streams */ }
}
?>