<?xml version="1.0" encoding="Shift_JIS"?>
<?xml-stylesheet type="text/css" href="ruby.css"?>
<xsl:stylesheet version="1.0"
     xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
     xmlns="http://www.w3.org/1999/xhtml">
 <xsl:template match="/">
  <html>
   <head>
    <title><xsl:value-of select="/circleinfo/title" /></title>
    <link rel="stylesheet" type="text/css" href="../ruby.css" />
   </head>
  <body>
    <h2><xsl:apply-templates select="/circleinfo/title" /></h2>
    <p><xsl:apply-templates select="/circleinfo/abstract" /></p>
    <xsl:if test="count(circleinfo/schedule)>0">
     <h3>現在の参加予定</h3>
     <p>
      <table border="1">
       <xsl:apply-templates select="/circleinfo/schedule" />
      </table>
     </p>
    </xsl:if>
    <xsl:if test="count(circleinfo/publishing)>0">
     <h3>発行された本</h3>
     <p>
      <table border="1">
       <xsl:apply-templates select="/circleinfo/publishing" />
      </table>
     </p>
    </xsl:if>
    <xsl:if test="count(circleinfo/other)>0">
     <h3>その他活動結果</h3>
     <p>
      <table border="1">
       <xsl:apply-templates select="/circleinfo/other" />
      </table>
     </p>
    </xsl:if>
  </body>
  </html>
 </xsl:template>

 <!-- テーブル生成 -->
 <xsl:template match="/circleinfo/schedule|/circleinfo/publishing|/circleinfo/other">
    <tr>
     <xsl:if test="count(image)>0">
      <td style="text-align:center">
       <img>
        <xsl:attribute name="src">
         <xsl:value-of select="image" />
        </xsl:attribute>
        <xsl:attribute name="alt">
         <xsl:value-of select="title" />
        </xsl:attribute>
       </img>
      </td>
     </xsl:if>
     <td>
      <xsl:attribute name="colspan">
       <xsl:value-of select="2-count(image)" />
      </xsl:attribute>
      <xsl:if test="count(title)>0">
       <b><xsl:apply-templates select="title" /></b><br />
      </xsl:if>
      <xsl:if test="genle or contents or style">
       <xsl:apply-templates select="genle" />
       <xsl:if test="genle and contents">：</xsl:if>
       <xsl:apply-templates select="contents" />
       <xsl:apply-templates select="style" />
       <br />
      </xsl:if>
      <xsl:apply-templates select="place" /><xsl:apply-templates select="space" /><xsl:apply-templates select="date" /><br />
     <xsl:if test="count(footnote)>0">
      <br /><xsl:apply-templates select="footnote" />
     </xsl:if>
     </td>
    </tr>
 </xsl:template>

 <!-- style (with "（" and "）" bracket -->
 <xsl:template match="style">
  <xsl:if test="count(text())>0">
   （<xsl:apply-templates />）
  </xsl:if>
 </xsl:template>

 <!-- place / space (with url options and ", " letter) -->
 <xsl:template match="place|space">
  <xsl:choose>
   <xsl:when test="@url">
    <a>
     <xsl:attribute name="href">
      <xsl:value-of select="@url" />
     </xsl:attribute>
     <xsl:apply-templates />
    </a>
   </xsl:when>
   <xsl:otherwise>
     <xsl:apply-templates />
   </xsl:otherwise>
  </xsl:choose>, 
 </xsl:template>

 <!-- footnote (with url options) -->
 <xsl:template match="footnote">
  <xsl:choose>
   <xsl:when test="@url">
    <a>
     <xsl:attribute name="href">
      <xsl:value-of select="@url" />
     </xsl:attribute>
     <xsl:apply-templates />
    </a>
   </xsl:when>
   <xsl:otherwise>
     <xsl:apply-templates />
   </xsl:otherwise>
  </xsl:choose>
 </xsl:template>

 <!-- general (text / ruby) -->
 <xsl:template match="ruby">
  <xsl:if test="count(@text)>0">
   <ruby>
    <rb>
     <xsl:apply-templates />
    </rb>
    <rp>
     <xsl:choose>
      <xsl:when test="count(@lbracket)=0">[</xsl:when>
      <xsl:otherwise><xsl:value-of select="@lbracket" /></xsl:otherwise>
     </xsl:choose>
    </rp>
    <rt><xsl:value-of select="@text" /></rt>
    <rp>
     <xsl:choose>
      <xsl:when test="count(@rbracket)=0">]</xsl:when>
      <xsl:otherwise><xsl:value-of select="@rbracket" /></xsl:otherwise>
     </xsl:choose>
    </rp>
   </ruby>
  </xsl:if>
 </xsl:template>

 <xsl:template match="ret">
  <br />
 </xsl:template>

 <xsl:template match="text()">
  <xsl:value-of select="." />
 </xsl:template>

</xsl:stylesheet>
