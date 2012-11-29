<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:template match="/">       
        <h2>Segmentation</h2>
        <table border="1">
            <tr bgcolor="#9acd32">
                <th>Keyword</th>
                <th>Revenue</th>
                <th>Visits</th>
                <th>Bounce Rate</th>
                <th>Actions</th>
            </tr>
            <xsl:for-each select="result/row">
                <tr>
                    <td>
                        <xsl:value-of select="label"/>
                    </td>
                    <td>
                        <xsl:value-of select="revenue"/>
                    </td>
                    <td>
                        <xsl:value-of select="nb_visits"/>
                    </td>
                    <td>
                        <xsl:value-of select="bounce_count"/>
                    </td>
                    <td>
                        <table border="1">
                            <tr bgcolor="#9acd32">
                                <th>Name</th>
                                <th>Conversion</th>
                                <th>Revenue</th>
                                <th>Items</th>
                            </tr>
                            <xsl:for-each select="goals/row">
                                <tr>
                                    <td>
                                        <xsl:value-of select="@idgoal"/>
                                    </td>      
                                    <td>
                                        <xsl:value-of select="nb_conversions"/>
                                    </td>    
                                    <td>
                                        <xsl:value-of select="revenue"/>
                                    </td>   
                                    <td>
                                        <xsl:value-of select="items"/>
                                    </td>     
                                </tr>    
                            </xsl:for-each>
                        </table>
                    </td>
                </tr>
            </xsl:for-each>
        </table>
    </xsl:template>
</xsl:stylesheet>

