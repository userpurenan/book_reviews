import React from 'react';
import { useState } from 'react';
import { useForm } from 'react-hook-form'; //「Formik」よりも「react-hook-form」の方がバリデーションの設定が少なくて良いと思う。
import './test.scss';


export const Test = () => {
	const { register, handleSubmit, formState: { errors } } = useForm(); // バリデーションのフォームを定義。
    const [Message, setMassage] = useState('');
    const [name, setName] = useState('');
    const handleNameChange = (e) => setName(e.target.value);

    const viewMessage = () => {
        setMassage(`${name}か、、良い名前だね！`);
    }

    return(
        <div>
            <h1>テスト</h1>
        <form onSubmit={handleSubmit(viewMessage)}>    
            <label>名前を入力してね！</label>
            <br />
            <input 
                type="text" 
                {...register("name", {required: true })}
                onChange={handleNameChange}
                placeholder='名前を入力'
            />
            {/* 以下はバリデーションエラーが発生したときに表示されるエラー文 */}
            <p id='errorMessage'>{errors.name?.type === 'required' && <b className='error'>※名前を入力してください！</b> }</p>
            <button type="submit">
            push!!
          </button>
        </form>
        <p id='Message'>{Message}</p>
        </div>
    )
}
