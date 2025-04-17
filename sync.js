// Sistema de armazenamento simples usando arquivo de texto
const DATA_SYNC = {
    // Arquivo para sincronização de dados
    DATA_FILE: 'data-connector.php',
    
    // Carregar todos os dados
    loadData: async function() {
        try {
            console.log('Tentando carregar dados do arquivo:', this.DATA_FILE);
            
            const response = await fetch(this.DATA_FILE + '?t=' + new Date().getTime(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache'
                }
            });
            
            if (!response.ok) {
                console.error('Resposta não ok ao carregar dados:', response.status, response.statusText);
                throw new Error('Falha ao carregar dados: ' + response.status);
            }
            
            const data = await response.json();
            console.log('Dados carregados com sucesso. Submissions:', 
                        data.submissions ? data.submissions.length : 0, 
                        'Leads:', data.leads ? data.leads.length : 0);
            return data;
        } catch (error) {
            console.error('Erro ao carregar dados:', error);
            // Retorna dados vazios em caso de erro
            return { submissions: [], leads: [] };
        }
    },
    
    // Salvar todos os dados
    saveData: async function(data) {
        try {
            console.log('Tentando salvar dados no arquivo:', this.DATA_FILE);
            console.log('Dados a salvar - Submissions:', data.submissions.length, 'Leads:', data.leads.length);
            
            const response = await fetch(this.DATA_FILE, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache'
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                console.error('Resposta não ok ao salvar dados:', response.status, response.statusText);
                throw new Error('Falha ao salvar dados: ' + response.status);
            }
            
            const result = await response.json();
            console.log('Resposta do servidor ao salvar dados:', result);
            return result.sucesso || false;
        } catch (error) {
            console.error('Erro ao salvar dados:', error);
            return false;
        }
    },
    
    // Salvar um novo formulário preenchido
    saveSubmission: async function(submission, lead) {
        console.log('Tentando salvar novo envio de formulário');
        
        try {
            // Primeiro carrega dados existentes
            const data = await this.loadData();
            console.log('Dados existentes carregados para adicionar novo envio');
            
            // Adicionar submissão
            data.submissions.push(submission);
            
            // Adicionar lead
            data.leads.push(lead);
            
            console.log('Novo envio e lead adicionados aos dados. Salvando...');
            
            // Salvar dados
            const result = await this.saveData(data);
            console.log('Resultado do salvamento de novo envio:', result ? 'Sucesso' : 'Falha');
            return result;
        } catch (error) {
            console.error('Erro ao salvar novo envio:', error);
            return false;
        }
    },
    
    // Atualizar o status de um lead
    updateLead: async function(leadId, newStatus, note) {
        console.log('Tentando atualizar lead:', leadId);
        
        try {
            const data = await this.loadData();
            let updated = false;
            
            // Encontrar e atualizar o lead
            for (let i = 0; i < data.leads.length; i++) {
                if (data.leads[i].id === leadId) {
                    console.log('Lead encontrado. Atualizando status de', data.leads[i].status, 'para', newStatus);
                    
                    // Atualizar status
                    data.leads[i].status = newStatus;
                    data.leads[i].ultimaAtualizacao = new Date().toISOString();
                    
                    // Adicionar nota
                    if (note && note.trim() !== '') {
                        if (!data.leads[i].notas) {
                            data.leads[i].notas = [];
                        }
                        
                        data.leads[i].notas.push({
                            data: new Date().toISOString(),
                            status: newStatus,
                            texto: note
                        });
                        console.log('Nota adicionada ao lead');
                    }
                    
                    updated = true;
                    break;
                }
            }
            
            if (updated) {
                console.log('Lead atualizado. Salvando dados...');
                // Salvar dados atualizados
                return await this.saveData(data);
            } else {
                console.log('Lead não encontrado para atualização:', leadId);
            }
            
            return false;
        } catch (error) {
            console.error('Erro ao atualizar lead:', error);
            return false;
        }
    },
    
    // Excluir um registro
    deleteEntry: async function(id, type = 'submission') {
        console.log('Tentando excluir', type, 'com ID:', id);
        
        try {
            const data = await this.loadData();
            let deleted = false;
            
            if (type === 'lead') {
                // Remover o lead
                const originalLength = data.leads.length;
                const newLeads = data.leads.filter(lead => lead.id !== id);
                
                if (newLeads.length !== originalLength) {
                    console.log('Lead encontrado e removido');
                    data.leads = newLeads;
                    deleted = true;
                } else {
                    console.log('Lead não encontrado para exclusão');
                }
            } else {
                // Remover a submissão
                const originalLength = data.submissions.length;
                const newSubmissions = data.submissions.filter(sub => sub.id !== id);
                
                if (newSubmissions.length !== originalLength) {
                    console.log('Submission encontrada e removida');
                    data.submissions = newSubmissions;
                    deleted = true;
                } else {
                    console.log('Submission não encontrada para exclusão');
                }
            }
            
            if (deleted) {
                console.log('Registro excluído. Salvando dados atualizados...');
                // Salvar dados atualizados
                return await this.saveData(data);
            }
            
            return false;
        } catch (error) {
            console.error('Erro ao excluir registro:', error);
            return false;
        }
    }
}; 