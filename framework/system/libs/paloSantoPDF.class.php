<?php
require("fpdf/fpdf.php");
define('FPDF_FONTPATH','libs/fpdf/font/');
class paloPDF extends FPDF
{    
    var $wMaxCol=0;
    var $widthCol;
    var $widthTable=0;	//valor maximo que puede tomar la tabla
    var $nCol=0;
    var $title;
    var $addpage=false;
    var $image="";
    var $colorHeaderTable;
    var $colorBackg;
    var $font;
    var $formatPage;

    function Header(){
        parent::SetFont($this->getFont(),'',18);
        $this->SetTextColor(255,255,255);
        $this->SetFillColor($this->colorBackg[0],$this->colorBackg[1],$this->colorBackg[2]);
        //Título
	$tam=$this->w-3;
	$pX=($this->w-$tam)/2;
	$this->SetX($pX);
	parent::Cell($tam,15,$this->getTitle(),0,0,'R',true);
	if($this->getLogoHeader()!="")
	   parent::Image($this->getLogoHeader(),$pX,11,40);
	//Salto de línea
	parent::Ln(20);
	parent::SetFont($this->getFont(),'',10);
    }

    function Footer(){
        //Posición a 1,5 cm del final
	$this->SetY(-15);
	//Arial itálica 8
	$this->SetFont($this->getFont(),'',9);
	//Color del texto en gris
	$this->SetTextColor(128);
	//Número de página
	$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }

    //Tabla simple
    function BasicTable($header,$data){
	if(isset($header)&&isset($data)&&count($header)>0&&count($data)>0)
	{
	   $this->setWidthColumns($data);
	   $this->nCol=count($header);
	   $this->setMaxCol();
	   parent::SetFont($this->getFont(),'',10);
	   $this->validateSizeColumns($header);
	   $i=0;
	   $this->setFormatCabecera();
	   $this->SetFillColor($this->colorHeaderTable[0],$this->colorHeaderTable[1],$this->colorHeaderTable[2]);
	   $this->Row($header,0,true,'C');
	   //Datos
	   $this->setFormatDatos();
	   $this->SetFillColor(244,244,244);
	   $fill=false;
	   foreach($data as $row)
	   {	  
               $this->addpage=$this->CheckPageBreak($this->calculateHeight($row),false);
	           if($this->addpage)
                    {   
                        $pageAdd=$this->CheckPageBreak($this->calculateHeight($row),$this->addpage);
                        $this->addpage=$pageAdd;
                        $this->SetFillColor($this->colorHeaderTable[0],$this->colorHeaderTable[1],$this->colorHeaderTable[2]);
                        $this->setFormatCabecera();
                        $this->Row($header,0,true,'C');
                        $this->setFormatDatos();
                        $this->SetFillColor(244,244,244);
                    }
                $this->Row($row,1,$fill,'J');
                $fill=!$fill;
            }			
        }else
            $this->Cell(40,6,"No hay datos que mostrar",0);	
    }

    //Functions SET
    function setOrientation ($orientation){
        if($orientation=='P' || $orientation=='L')
        {
           $this->CurPageFormat=$orientation;
	   $this->DefOrientation=$orientation;
        }else
	   $this->Error('Incorrect orientation: '.$orientation);
    }
 
    function setTitle ($title){
        $this->title=$title;
    }

    function setLogoHeader($urlImage){
        $this->image=$urlImage;
    }	

    function setColorHeader($color){
        $this->colorBackg=$color;
    }

    function setColorHeaderTable($color){
        $this->colorHeaderTable=$color;
    }

    function setFormatCabecera(){
        parent::SetFont($this->getFont(),'',10);
	$this->SetTextColor(255,255,255);
    }

    function setFormatDatos(){
        parent::SetFont($this->getFont(),'',10);
	$this->SetTextColor(0,0,0);
    }

    function setFont($font){
	$this->font=$font;
    }

    //Functions GET
    function getLogoHeader(){
        return $this->image;
    }

    function getColorHeader(){
        return $this->colorBackg;
    }

    function getTitle (){
        return $this->title;
    }

    function getColorHeaderTable($color){
        return $this->colorHeaderTable;
    }

    function getFont(){
        return $this->font;
    }

    function printTable($nameFile,$title,$header,$data){
        $this->setTitle($title);
        $this->AliasNbPages();
	$this->AddFont('Verdana','','verdana.php');
	parent::AddPage();
	$this->SetLineWidth(0.05);
	$this->SetDrawColor(153,153,153);
        $this->setWidthTable();
	$this->BasicTable($header,$data);
	parent::Output($nameFile,'D');
    }

    function setWidthColumns($data){
        $wCol=array();
        for($i=0;$i<count($data[0]);$i++){
            $wRow=array();
            foreach($data as $row)
            {
                $wTemp=round(parent::GetStringWidth($row[$i]))+3;
                $wRow[]=$wTemp;
            }
            $wCol[]=$wRow;	
	}
        foreach($wCol as $row){
            $this->widthCol[]=max($row);
	}
    }

    function setWidthTable(){
    $this->widthTable=$this->w -3;
    }

    function setFormat($format)
    {
        if(is_string($format))
	   $format=$this->_getpageformat($format);
            
        $this->DefPageFormat=$format;
	$this->CurPageFormat=$format;
	//Page orientation
        $orientation=strtolower($this->DefOrientation);
	if($orientation=='p' || $orientation=='portrait')
	{
	   $this->w=$this->DefPageFormat[0];
	   $this->h=$this->DefPageFormat[1];
	}
	else
	{
	   $this->w=$this->DefPageFormat[1];
	   $this->h=$this->DefPageFormat[0];
	}
        $this->PageBreakTrigger=$this->h-$this->bMargin;
    }

    function setMaxCol(){
        $this->wMaxCol=(int)($this->widthTable/$this->nCol);
        //echo "tabla: {$this->widthTable}, col: {$this->nCol}, max: {$this->wMaxCol}";
    }

    function validateSizeColumns($header){
        $i=0;
        $plus=0;
        $wmax=$this->wMaxCol;
        foreach($this->widthCol as $colS)
        {	
            $size=round(parent::GetStringWidth($header[$i]["name"]))+3;
            //echo parent::GetStringWidth($header[$i]["name"]);
            //echo $header[$i]["name"];
            $this->widthCol[$i]=max($colS,$size);        
          //  echo "head: {$size}, col: {$colS}";
            if($plus>0)
                $wmax=$wmax+$plus;
             if($this->widthCol[$i]>$wmax){
                $this->widthCol[$i]=$wmax;
            }
            $plus=$wmax-$this->widthCol[$i];
            //echo "plus: {$plus}, col: {$this->widthCol[$i]}, maxvar: {$wmax}, maxfijo:{$this->wMaxCol}";
            $wmax=$this->wMaxCol;
            $i++;
        }
    }

    function CheckPageBreak($h,$addpage)
    {
        //If the height h would cause an overflow, add a new page immediately
        if($addpage){
            $this->AddPage($this->CurOrientation);
            return false;
        }
        if($this->GetY()+$h>$this->PageBreakTrigger )
            return true;
        else 
            return false; 
    }

    function NbLines($w,$txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb)
        {
            $c=$s[$i];
            if($c=="\n")
            {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }

    function Row($data1,$type,$color,$a)
    {
        //Calculate the height of the row
        $data=array();
        if($type==0)
        {                   
            foreach($data1 as $col)
                $data[]=$col["name"];
        }
        else
            $data=$data1;
        $h=$this->calculateHeight($data);    
        //$this->addpage=$this->CheckPageBreak($h,$this->addpage);
        //Issue a page break first if needed
        //$this->CheckPageBreak($h);
        //Draw the cells of the row
        $this->centrarTabla();		
        for($i=0;$i<count($data);$i++)
        {
            $w=$this->widthCol[$i];
            //Save the current position
            $x=$this->GetX();
            $y=$this->GetY();
            //Draw the border
            if($color)
                $this->Rect($x,$y,$w,$h,'DF');
            else
                $this->Rect($x,$y,$w,$h);
            //Print the text
            $this->MultiCell($w,5, utf8_decode(rtrim($data[$i])),0,$a);
            //Put the position to the right of the cell
            $this->SetXY($x+$w,$y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    function calculateHeight($data){
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widthCol[$i],$data[$i]));
        $h=5*$nb;
        return $h;
    }

    function centrarTabla(){
        $pX=($this->w-array_sum($this->widthCol))/2;
        $this->SetX($pX);
    }
}
?>
