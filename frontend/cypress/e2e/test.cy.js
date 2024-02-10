describe('ボタンをクリックしたとき', () => {
  describe('名前を入力していない時', () => {
    it('エラーメッセージが表示される', () => {
      cy.visit('http://localhost:3000/test')
      cy.get('button[type=submit]').click()
      cy.get('#errorMessage').should('have.text', '※名前を入力してください！')// エラーメッセージが表示されたか確認
    })
  })
  describe('名前が入力されている時', () => {
    it('正しいメッセージが表示されること', () => {
      cy.visit('http://localhost:3000/test')
      cy.get('input[type=text]').type('中嶋蓮')
      cy.get('button[type=submit]').click()
      cy.get('#Message').should('have.text', '中嶋蓮か、、良い名前だね！')// 正しいメッセージが表示された確認
    })  
  })
})